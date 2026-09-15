<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebhookService
{
    public static function dispatch(string $event, array $payload, ?int $userId = null): void
    {
        $query = Webhook::where('is_active', true);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $webhooks = $query->get()->filter(fn ($webhook) => $webhook->listensToEvent($event));

        foreach ($webhooks as $webhook) {
            self::deliver($webhook, $event, $payload);
        }
    }

    public static function deliver(Webhook $webhook, string $event, array $payload): void
    {
        $body = [
            'event' => $event,
            'data' => $payload,
            'timestamp' => now()->toISOString(),
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Event' => $event,
            'X-Webhook-Signature' => self::generateSignature($body, $webhook->secret),
        ];

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $body,
            'success' => false,
            'delivered_at' => now(),
        ]);

        try {
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($webhook->url, $body);

            $delivery->update([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_body' => Str::limit($response->body(), 2000),
            ]);

            $webhook->update([
                'last_delivery' => [
                    'event' => $event,
                    'status' => $response->status(),
                    'at' => now()->toISOString(),
                ],
                'failure_count' => $response->successful() ? 0 : $webhook->failure_count + 1,
            ]);

        } catch (\Throwable $e) {
            $delivery->update([
                'success' => false,
                'error' => Str::limit($e->getMessage(), 1000),
            ]);

            $webhook->update([
                'failure_count' => $webhook->failure_count + 1,
                'last_delivery' => [
                    'event' => $event,
                    'status' => 'error',
                    'at' => now()->toISOString(),
                ],
            ]);

            Log::error("Webhook delivery failed: {$webhook->id}", [
                'error' => $e->getMessage(),
                'url' => $webhook->url,
            ]);
        }
    }

    public static function generateSignature(array $payload, ?string $secret): string
    {
        if (!$secret) {
            return '';
        }
        $hmac = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $secret);
        return 'sha256=' . $hmac;
    }

    public static function verifySignature(string $body, string $signature, string $secret): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', $body, $secret);
        return hash_equals($expected, $signature);
    }
}
