<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_datetime',
        'estimated_duration',
        'scheduled_for',
        'project',
        'parent_task_id',
        'recurring_rule',
        'metadata',
    ];

    protected $casts = [
        'due_datetime' => 'datetime',
        'scheduled_for' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = ['priority_label', 'status_label'];

    public function getPriorityLabelAttribute(): string
    {
        return [1 => 'Low', 2 => 'Below Normal', 3 => 'Normal', 4 => 'High', 5 => 'Critical'][$this->priority] ?? 'Normal';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'snoozed' => 'Snoozed',
        ][$this->status] ?? $this->status;
    }

    public function getProgressAttribute(): int
    {
        $total = $this->items->count();
        if ($total === 0) return 0;
        $completed = $this->items->where('completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }

    public function getDueDateAttribute(): string
    {
        return $this->due_datetime?->format('Y-m-d') ?? '';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (config('tasks.disable_random_task_ids') != 'true') {
                $numberOfDigits = config('tasks.task_id_length') ?? 9;
                $minIdValue = 10 ** ($numberOfDigits - 1);
                $maxIdValue = 10 ** $numberOfDigits - 1;
                do {
                    $randomId = rand($minIdValue, $maxIdValue);
                } while (Task::find($randomId));
                $task->id = $randomId;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function items()
    {
        return $this->hasMany(TaskItem::class)->orderBy('sort_order');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 4);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
