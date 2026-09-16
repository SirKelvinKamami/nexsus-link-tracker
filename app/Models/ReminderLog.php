<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reminder_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function reminder()
    {
        return $this->belongsTo(Reminder::class);
    }
}
