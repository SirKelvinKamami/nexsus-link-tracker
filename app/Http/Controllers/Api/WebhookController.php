<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Webhook;
use App\Services\WebhookService;

class WebhookController extends BaseController
{
    /**
     * List all webhooks for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $this->getUserId();
        $perPage = $request->input('per_page', 50);

        $webhooks = Webhook::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        $webhooks->getCollection()->transform(function ($webhook) {
            return [
                'id' => $webhook->id,
                'name' => $webhook->name,
                'url' => $webhook->url,
                'is_active' => $webhook->is_active,
                'events' => $webhook->events,
                'failure_count' => $webhook->failure_count,
                'last_delivery' => $webhook->last_delivery,
                'created_at' => $webhook->created_at->toISOString(),
                'updated_at' => $webhook->updated_at->toISOString(),
            ];
        });

        return $this->success($webhooks);
    }

    /**
     * Get a single webhook with recent deliveries.
     */
    public function show($id)
    {
        $userId = $this->getUserId();

        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$webhook) {
            return $this->error('Webhook not found', 404);
        }

        $deliveries = $webhook->deliveries()
            ->limit(20)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'event' => $d->event,
                'success' => $d->success,
                'status_code' => $d->status_code,
                'error' => $d->error,
                'delivered_at' => $d->delivered_at->toISOString(),
            ]);

        $data = [
            'id' => $webhook->id,
            'name' => $webhook->name,
            'url' => $webhook->url,
            'is_active' => $webhook->is_active,
            'events' => $webhook->events,
            'failure_count' => $webhook->failure_count,
            'last_delivery' => $webhook->last_delivery,
            'deliveries' => $deliveries,
            'created_at' => $webhook->created_at->toISOString(),
            'updated_at' => $webhook->updated_at->toISOString(),
        ];

        return $this->success($data);
    }

    /**
     * Create a new webhook.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string|in:click,form_submit,page_visit,document_download',
        ]);

        $userId = $this->getUserId();
        $secret = 'whsec_' . bin2hex(random_bytes(32));

        $webhook = Webhook::create([
            'user_id' => $userId,
            'name' => $request->name,
            'url' => $request->url,
            'secret' => $secret,
            'is_active' => true,
            'events' => $request->events ?? ['click', 'form_submit', 'page_visit'],
            'failure_count' => 0,
        ]);

        $data = [
            'id' => $webhook->id,
            'name' => $webhook->name,
            'url' => $webhook->url,
            'secret' => $secret,
            'is_active' => $webhook->is_active,
            'events' => $webhook->events,
            'created_at' => $webhook->created_at->toISOString(),
        ];

        return $this->success($data, 201);
    }

    /**
     * Update a webhook.
     */
    public function update(Request $request, $id)
    {
        $userId = $this->getUserId();

        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$webhook) {
            return $this->error('Webhook not found', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string|in:click,form_submit,page_visit,document_download',
            'is_active' => 'boolean',
        ]);

        $webhook->update([
            'name' => $request->name,
            'url' => $request->url,
            'events' => $request->events ?? ['click', 'form_submit', 'page_visit'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->success([
            'id' => $webhook->id,
            'name' => $webhook->name,
            'url' => $webhook->url,
            'is_active' => $webhook->is_active,
            'events' => $webhook->events,
            'updated_at' => $webhook->updated_at->toISOString(),
        ]);
    }

    /**
     * Delete a webhook.
     */
    public function destroy($id)
    {
        $userId = $this->getUserId();

        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$webhook) {
            return $this->error('Webhook not found', 404);
        }

        $webhook->delete();

        return $this->success(['deleted' => true]);
    }

    /**
     * Test a webhook.
     */
    public function test($id)
    {
        $userId = $this->getUserId();

        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$webhook) {
            return $this->error('Webhook not found', 404);
        }

        WebhookService::deliver($webhook, 'test', [
            'message' => 'This is a test webhook delivery',
            'timestamp' => now()->toISOString(),
        ]);

        return $this->success(['delivered' => true]);
    }
}
