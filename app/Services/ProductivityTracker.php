<?php

namespace App\Services;

use App\Models\Task;

class ProductivityTracker
{
    /**
     * Get task analytics overview for a user.
     *
     * @return array{tasks_created: int, tasks_completed: int, avg_priority: float, completion_rate: float}
     */
    public function overview($userId, string $period = '30d'): array
    {
        $query = Task::forUser($userId);

        $periodDays = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $startDate = now()->subDays($periodDays - 1)->startOfDay();

        $tasksCreated = (clone $query)
            ->where('created_at', '>=', $startDate)
            ->count();

        $tasksCompleted = (clone $query)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->count();

        $avgPriority = (clone $query)
            ->where('created_at', '>=', $startDate)
            ->avg('priority');

        $totalTasks = (clone $query)->count();
        $completedTasks = (clone $query)->where('status', 'completed')->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        return [
            'tasks_created' => $tasksCreated,
            'tasks_completed' => $tasksCompleted,
            'avg_priority' => $avgPriority ? round((float) $avgPriority, 1) : 0,
            'completion_rate' => $completionRate,
            'period' => $period,
        ];
    }

    /**
     * Get completion trend data (daily completion rate over time).
     *
     * @return array<array{date: string, completed: int, created: int, rate: float}>
     */
    public function completionTrend($userId, int $days = 7): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $dailyData = Task::forUser($userId)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->selectRaw('COUNT(*) as created')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $day = $dailyData->firstWhere('date', $d);
            $created = $day ? (int) $day->created : 0;
            $completed = $day ? (int) $day->completed : 0;
            $rate = $created > 0 ? round(($completed / $created) * 100, 1) : 0;

            $trend[] = [
                'date' => $d,
                'completed' => $completed,
                'created' => $created,
                'rate' => $rate,
            ];
        }

        return $trend;
    }

    /**
     * Get priority distribution for a user.
     *
     * @return array<array{priority: int, label: string, count: int}>
     */
    public function priorityDistribution($userId): array
    {
        $labels = [
            1 => 'Low',
            2 => 'Below Normal',
            3 => 'Normal',
            4 => 'High',
            5 => 'Critical',
        ];

        $distribution = Task::forUser($userId)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $result = [];
        for ($p = 1; $p <= 5; $p++) {
            $result[] = [
                'priority' => $p,
                'label' => $labels[$p],
                'count' => (int) ($distribution[$p] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Calculate productivity score (0-100) for a user.
     *
     * @return array{score: int, factors: array, insights: array}
     */
    public function productivityScore($userId): array
    {
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();

        $totalTasks = Task::forUser($userId)->count();
        $completedThisWeek = Task::forUser($userId)
            ->where('updated_at', '>=', $startOfWeek)
            ->where('status', 'completed')
            ->count();
        $createdThisWeek = Task::forUser($userId)
            ->where('created_at', '>=', $startOfWeek)
            ->count();

        $overdue = Task::forUser($userId)
            ->where('due_datetime', '<', $now)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $highPriority = Task::forUser($userId)
            ->where('priority', '>=', 4)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $completedAll = Task::forUser($userId)
            ->where('status', 'completed')
            ->count();

        // Calculate score components (0-100 each)
        $completionScore = $totalTasks > 0 ? min(100, ($completedAll / $totalTasks) * 100) : 0;

        $weeklyActivityScore = min(100, ($completedThisWeek / max(1, $createdThisWeek)) * 100);

        $overduePenalty = min(100, $overdue * 10);
        $overdueScore = max(0, 100 - $overduePenalty);

        $priorityScore = $highPriority > 0 ? 60 : 100;

        $factors = [
            'completion_rate' => round($completionScore, 1),
            'weekly_activity' => round($weeklyActivityScore, 1),
            'overdue_health' => round($overdueScore, 1),
            'priority_management' => round($priorityScore, 1),
        ];

        $score = (int) round(
            ($completionScore * 0.35) +
            ($weeklyActivityScore * 0.25) +
            ($overdueScore * 0.25) +
            ($priorityScore * 0.15)
        );

        $insights = [];
        if ($completionScore >= 80) {
            $insights[] = 'Excellent completion rate — keep it up!';
        } elseif ($completionScore >= 50) {
            $insights[] = 'Good progress — try to complete more pending tasks.';
        } else {
            $insights[] = 'Low completion rate — focus on clearing pending tasks.';
        }

        if ($overdue > 0) {
            $insights[] = "You have {$overdue} overdue task(s) — address these first.";
        }

        if ($highPriority > 0) {
            $insights[] = "You have {$highPriority} high-priority task(s) — protect your focus time.";
        }

        $peakHours = $this->detectPeakHours($userId);
        if ($peakHours) {
            $insights[] = "Your most productive hours are {$peakHours} — protect this time.";
        }

        return [
            'score' => $score,
            'factors' => $factors,
            'insights' => $insights,
        ];
    }

    /**
     * Get focus time (total time spent on tasks this week in hours).
     */
    public function focusTime($userId): float
    {
        $startOfWeek = now()->copy()->startOfWeek();

        $totalMinutes = Task::forUser($userId)
            ->where('updated_at', '>=', $startOfWeek)
            ->where('status', 'completed')
            ->sum('estimated_duration');

        return round($totalMinutes / 60, 1);
    }

    /**
     * Detect peak productivity hours based on task completion patterns.
     */
    private function detectPeakHours($userId): ?string
    {
        $driver = \DB::connection()->getDriverName();
        $hourRaw = $driver === 'sqlite'
            ? "strftime('%H', updated_at)"
            : "HOUR(updated_at)";

        $hourCounts = Task::forUser($userId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(30))
            ->selectRaw("$hourRaw as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(2)
            ->pluck('hour');

        if ($hourCounts->isEmpty()) {
            return null;
        }

        $hours = $hourCounts->sort()->values();
        if ($hours->count() >= 2) {
            return $hours[0] . '–' . $hours[1] . ' AM';
        }

        return $hours[0] . ':00 AM';
    }

    /**
     * Get top completed tasks for analytics display.
     *
     * @return \Illuminate\Support\Collection
     */
    public function topCompletedTasks($userId, int $limit = 5)
    {
        return Task::forUser($userId)
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(30))
            ->orderByDesc('updated_at')
            ->take($limit)
            ->select('id', 'title', 'project', 'estimated_duration', 'updated_at', 'priority')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project' => $task->project ?? 'General',
                    'duration' => $task->estimated_duration ?? 0,
                    'duration_formatted' => $this->formatDuration($task->estimated_duration ?? 0),
                    'date' => $task->updated_at->toDateString(),
                    'priority' => $task->priority,
                ];
            });
    }

    /**
     * Get task completion statistics for yesterday's review.
     *
     * @return array{completed: int, total: int, time_spent_hours: float, overdue: int, rate: int}
     */
    public function yesterdayReview($userId): array
    {
        $yesterday = now()->copy()->subDay();

        $completed = Task::forUser($userId)
            ->whereDate('updated_at', $yesterday->toDateString())
            ->where('status', 'completed')
            ->count();

        $total = Task::forUser($userId)
            ->whereDate('updated_at', $yesterday->toDateString())
            ->count();

        $timeSpentMinutes = Task::forUser($userId)
            ->whereDate('updated_at', $yesterday->toDateString())
            ->where('status', 'completed')
            ->sum('estimated_duration');

        $overdue = Task::forUser($userId)
            ->where('due_datetime', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $rate = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return [
            'completed' => $completed,
            'total' => $total,
            'completed_display' => "{$completed}/{$total}",
            'time_spent_hours' => round($timeSpentMinutes / 60, 1),
            'overdue' => $overdue,
            'rate' => $rate,
        ];
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes >= 60) {
            $hours = (int) ($minutes / 60);
            $mins = $minutes % 60;
            return $mins > 0 ? "{$hours}h {$mins}m" : "{$hours}h";
        }
        return "{$minutes}m";
    }
}