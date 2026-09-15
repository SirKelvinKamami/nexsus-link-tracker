<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Webhook;
use App\Services\WebhookService;

class WebhookController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $webhooks = Webhook::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->get();

        return view('studio.webhooks.index', compact('webhooks'));
    }

    public function create()
    {
        return view('studio.webhooks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string|in:click,form_submit,page_visit,document_download',
        ]);

        $userId = Auth::id();
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

        return redirect()->route('webhooks.edit', $webhook->id)
            ->with('success', 'Webhook created. Save the secret — it won\'t be shown again: ' . $secret);
    }

    public function edit($id)
    {
        $userId = Auth::id();
        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $deliveries = $webhook->deliveries()->limit(20)->get();

        return view('studio.webhooks.edit', compact('webhook', 'deliveries'));
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

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

        return redirect()->route('webhooks.edit', $webhook->id)
            ->with('success', 'Webhook updated.');
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $webhook->delete();

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook deleted.');
    }

    public function test($id)
    {
        $userId = Auth::id();
        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        WebhookService::deliver($webhook, 'test', [
            'message' => 'This is a test webhook delivery',
            'timestamp' => now()->toISOString(),
        ]);

        return redirect()->route('webhooks.edit', $webhook->id)
            ->with('success', 'Test webhook delivered.');
    }

    public function redeliver($id, $deliveryId)
    {
        $userId = Auth::id();
        $webhook = Webhook::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $delivery = $webhook->deliveries()->where('id', $deliveryId)->firstOrFail();

        WebhookService::deliver($webhook, $delivery->event, $delivery->payload['data'] ?? []);

        return redirect()->route('webhooks.edit', $webhook->id)
            ->with('success', 'Webhook redelivered.');
    }
}
