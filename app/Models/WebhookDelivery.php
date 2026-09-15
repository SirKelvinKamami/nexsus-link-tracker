<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'success',
        'status_code',
        'response_body',
        'error',
        'delivered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'success' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
