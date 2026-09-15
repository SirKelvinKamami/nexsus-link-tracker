<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'url',
        'secret',
        'is_active',
        'events',
        'last_delivery',
        'failure_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'events' => 'array',
        'last_delivery' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class)->orderByDesc('delivered_at');
    }

    public function listensToEvent(string $event): bool
    {
        if (empty($this->events)) {
            return true;
        }
        return in_array($event, $this->events);
    }
}
