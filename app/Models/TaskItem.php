<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'completed',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function complete()
    {
        $this->update([
            'completed' => true,
            'completed_at' => now(),
        ]);
    }

    public function uncomplete()
    {
        $this->update([
            'completed' => false,
            'completed_at' => null,
        ]);
    }
}
