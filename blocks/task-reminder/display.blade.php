<div class="fadein">
@php
    $typeParams = json_decode($link->type_params, true) ?? [];
    $displayType = $typeParams['display_type'] ?? 'tasks';
    $maxItems = (int) ($typeParams['max_items'] ?? 10);
    $showPriority = ($typeParams['show_priority'] ?? 'yes') === 'yes';
    $showDueDate = ($typeParams['show_due_date'] ?? 'yes') === 'yes';
    $showProject = ($typeParams['show_project'] ?? 'yes') === 'yes';
    $statusFilter = $typeParams['status_filter'] ?? '';
    $priorityFilter = $typeParams['priority_filter'] ?? '';

    $userId = auth()->id();
    $priorityLabels = [1 => 'Low', 2 => 'Below Normal', 3 => 'Normal', 4 => 'High', 5 => 'Critical'];
    $priorityColors = [1 => '#059669', 2 => '#059669', 3 => '#4361ee', 4 => '#d97706', 5 => '#ef476f'];

    if ($userId) {
        if ($displayType === 'reminders') {
            $query = \App\Models\Task::forUser($userId)
                ->join('reminders', 'reminders.task_id', '=', 'tasks.id')
                ->where('reminders.user_id', $userId)
                ->where('reminders.sent', false)
                ->select('tasks.*');
        } else {
            $query = \App\Models\Task::forUser($userId);
        }

        if ($statusFilter) {
            $query->where('tasks.status', $statusFilter);
        }
        if ($priorityFilter) {
            $query->where('tasks.priority', (int) $priorityFilter);
        }

        $items = $query->orderByDesc('priority')->orderBy('due_datetime')->take($maxItems)->get();

        if ($displayType === 'both') {
            $reminderTaskIds = \App\Models\Reminder::whereIn('task_id', $items->pluck('id'))
                ->where('sent', false)->pluck('task_id');
            $items = $items->map(function ($task) use ($reminderTaskIds) {
                $task->has_reminder = $reminderTaskIds->contains($task->id);
                return $task;
            });
        }
    } else {
        $items = collect();
    }
@endphp

@if($items->isNotEmpty())
<div style="background:rgba(255,255,255,0.95);border:1px solid #e9ecef;border-radius:12px;padding:16px;backdrop-filter:blur(4px);">
    <h3 style="font-size:16px;font-weight:700;margin:0 0 12px 0;color:#1a1a2e;">
        @if($displayType === 'reminders') 🔔 Reminders @elseif($displayType === 'both') 📋 Tasks & Reminders @else 📋 Tasks @endif
    </h3>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($items as $task)
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8f9fa;border-radius:8px;border-left:3px solid {{ $priorityColors[$task->priority] ?? '#6b7280' }};">
            <span style="font-size:13px;font-weight:600;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
                @if($task->status === 'completed') text-decoration:line-through;opacity:0.5; @endif">
                {{ $task->title }}
                @if($displayType === 'both' && $task->has_reminder)
                <span style="font-size:10px;background:#ef476f20;color:#ef476f;padding:1px 6px;border-radius:9999px;margin-left:4px;">🔔</span>
                @endif
            </span>
            @if($showPriority)
            <span style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:9999px;
                background:rgba(67,97,238,0.1);color:#4361ee;">
                {{ $priorityLabels[$task->priority] ?? 'Normal' }}
            </span>
            @endif
            @if($showDueDate && $task->due_datetime)
            <span style="font-size:11px;color:#6c757d;white-space:nowrap;">
                📅 {{ $task->due_datetime->format('M d') }}
            </span>
            @endif
            @if($showProject && $task->project)
            <span style="font-size:11px;color:#6c757d;background:#f1f3f5;padding:2px 8px;border-radius:4px;">
                {{ $task->project }}
            </span>
            @endif
        </div>
        @endforeach
    </div>
    <a href="/studio/tasks" style="display:inline-block;margin-top:10px;font-size:12px;color:#4361ee;text-decoration:none;font-weight:600;">
        View All →
    </a>
</div>
@else
<div style="background:rgba(255,255,255,0.95);border:1px solid #e9ecef;border-radius:12px;padding:20px;text-align:center;color:#adb5bd;">
    <p style="margin:0;font-size:14px;">No tasks found</p>
</div>
@endif
</div>
