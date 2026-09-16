<div class="fadein">
@php
    $typeParams = json_decode($link->type_params, true) ?? [];
    $showStats = ($typeParams['show_stats'] ?? 'yes') === 'yes';
    $statCount = (int) ($typeParams['stat_count'] ?? 4);
    $showOverdue = ($typeParams['show_overdue'] ?? 'yes') === 'yes';
    $showCompleted = ($typeParams['show_completed'] ?? 'yes') === 'yes';

    $userId = auth()->id();
    $priorityLabels = [1 => 'Low', 2 => 'Below Normal', 3 => 'Normal', 4 => 'High', 5 => 'Critical'];
    $statCards = [];

    if ($userId) {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        $dueToday = \App\Models\Task::forUser($userId)
            ->whereBetween('due_datetime', [$today, $tomorrow])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $highPriority = \App\Models\Task::forUser($userId)
            ->where('priority', '>=', 4)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $inProgress = \App\Models\Task::forUser($userId)
            ->where('status', 'in_progress')
            ->count();

        $totalTasks = \App\Models\Task::forUser($userId)->count();
        $completedAll = \App\Models\Task::forUser($userId)->where('status', 'completed')->count();
        $completionRate = $totalTasks > 0 ? round(($completedAll / $totalTasks) * 100, 0) : 0;

        $statCards = [
            [
                'label' => "Today's Tasks",
                'value' => "{$dueToday}/5",
                'color' => '#4361ee',
                'icon' => 'calendar',
            ],
            [
                'label' => 'High Priority',
                'value' => $highPriority,
                'color' => '#ef476f',
                'icon' => 'flag',
            ],
            [
                'label' => 'In Progress',
                'value' => $inProgress,
                'color' => '#d97706',
                'icon' => 'play-circle',
            ],
            [
                'label' => 'Completion Rate',
                'value' => $completionRate . '%',
                'color' => '#06d6a0',
                'icon' => 'check-circle',
            ],
        ];

        if ($showOverdue) {
            $overdue = \App\Models\Task::forUser($userId)
                ->where('due_datetime', '<', $today)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count();
            $statCards[] = [
                'label' => 'Overdue',
                'value' => $overdue,
                'color' => '#ef476f',
                'icon' => 'exclamation-triangle',
            ];
        }

        if ($showCompleted) {
            $statCards[] = [
                'label' => 'Completed (30d)',
                'value' => \App\Models\Task::forUser($userId)
                    ->where('status', 'completed')
                    ->where('updated_at', '>=', now()->subDays(30))
                    ->count(),
                'color' => '#4361ee',
                'icon' => 'check',
            ];
        }

        $statCards = array_slice($statCards, 0, $statCount);
    }
@endphp

@if($userId && $statCards->isNotEmpty())
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));gap:12px;">
    @foreach($statCards as $card)
    <div style="background:rgba(255,255,255,0.95);border:1px solid #e9ecef;border-radius:12px;padding:16px;text-align:center;backdrop-filter:blur(4px);">
        <div style="font-size:24px;font-weight:800;color:{{ $card['color'] }};">
            {{ $card['value'] }}
        </div>
        <div style="font-size:11px;color:#6c757d;margin-top:4px;font-weight:500;">
            {{ $card['label'] }}
        </div>
    </div>
    @endforeach
</div>
@else
<div style="background:rgba(255,255,255,0.95);border:1px solid #e9ecef;border-radius:12px;padding:20px;text-align:center;color:#adb5bd;">
    <p style="margin:0;font-size:14px;">@if(!$userId) Sign in to view stats @else No tasks yet @endif</p>
</div>
@endif
</div>
