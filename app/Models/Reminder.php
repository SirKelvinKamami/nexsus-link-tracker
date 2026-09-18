<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'trigger_type',
        'trigger_value',
        'channel',
        'sent',
        'sent_at',
        'user_response',
        'responded_at',
    ];

    protected $casts = [
        'trigger_value' => 'datetime',
        'sent' => 'boolean',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = ['task_title', 'due_info', 'section'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function scopePending($query)
    {
        return $query->where('sent', false);
    }

    public function scopeDue($query)
    {
        return $query->where('trigger_value', '<=', now());
    }

    public function scopeByChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function getTaskTitleAttribute(): string
    {
        return $this->task?->title ?? 'Untitled Task';
    }

    public function getDueInfoAttribute(): string
    {
        if (!$this->trigger_value) return 'No due date';
        $diff = now()->diffInMinutes($this->trigger_value, false);
        if ($diff < 0) {
            return 'Overdue';
        } elseif ($diff < 60) {
            return $diff . ' min before due';
        } elseif ($diff < 1440) {
            return round($diff / 60) . 'h before due';
        } else {
            return 'Due ' . $this->trigger_value->format('M d, Y H:i');
        }
    }

    public function getSectionAttribute(): string
    {
        if (!$this->trigger_value) return 'upcoming';
        $diff = now()->diffInHours($this->trigger_value, false);
        if ($diff < 0) return 'past';
        if ($diff < 24) return 'today';
        return 'upcoming';
    }
}
