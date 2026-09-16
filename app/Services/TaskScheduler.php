<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskItem;
use Illuminate\Support\Facades\Log;

class TaskScheduler
{
    /**
     * Schedule a task optimally based on user's calendar, priorities, and patterns.
     *
     * @return array{scheduled_for: string, time_block_start: string, time_block_end: string, duration_minutes: int, confidence: float, reason: string}
     */
    public function schedule(Task $task, $user): array
    {
        $now = now();
        $workStart = $this->getUserWorkStart($user, $now);
        $workEnd = $this->getUserWorkEnd($user, $now);

        // Get existing tasks for the user today (excluding this task)
        $existingTasks = Task::where('user_id', $user->id)
            ->where('id', '!=', $task->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('scheduled_for', '>=', $now->startOfDay())
            ->whereDate('scheduled_for', '<=', $now->endOfDay())
            ->orderBy('scheduled_for')
            ->get();

        // Calculate available time blocks
        $availableBlocks = $this->findAvailableBlocks($workStart, $workEnd, $existingTasks);

        // Find best slot based on priority and task type
        $bestSlot = $this->findBestSlot($availableBlocks, $task, $now);

        if ($bestSlot === null) {
            // No slot today, suggest tomorrow or reschedule
            return [
                'scheduled_for' => null,
                'time_block_start' => null,
                'time_block_end' => null,
                'duration_minutes' => $task->estimated_duration ?? 30,
                'confidence' => 0.0,
                'reason' => 'No available slots today. Suggested for tomorrow morning.',
                'suggested_date' => $now->copy()->addDay()->startOfDay()->toDateTimeString(),
            ];
        }

        // Calculate confidence score
        $confidence = $this->calculateConfidence($bestSlot, $task, $existingTasks);

        return [
            'scheduled_for' => $bestSlot['start'],
            'time_block_start' => $bestSlot['start'],
            'time_block_end' => $bestSlot['end'],
            'duration_minutes' => $bestSlot['duration'],
            'confidence' => round($confidence, 2),
            'reason' => $this->generateReason($bestSlot, $task, $existingTasks),
        ];
    }

    /**
     * Recalculate scheduling for all pending tasks for a user.
     *
     * @return array{tasks_rescheduled: int, tasks_unscheduled: int, conflicts_resolved: int}
     */
    public function recalculateAll($user): array
    {
        $pendingTasks = Task::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNull('scheduled_for')
            ->orWhere('scheduled_for', '<', now()->subHour())
            ->get();

        $rescheduled = 0;
        $unscheduled = 0;
        $conflictsResolved = 0;

        foreach ($pendingTasks as $task) {
            try {
                $result = $this->schedule($task, $user);

                if ($result['scheduled_for']) {
                    $task->update([
                        'scheduled_for' => $result['scheduled_for'],
                        'metadata' => array_merge($task->metadata ?? [], [
                            'schedule_confidence' => $result['confidence'],
                            'schedule_reason' => $result['reason'],
                        ]),
                    ]);
                    $rescheduled++;
                } else {
                    $unscheduled++;
                }

                // Check for conflicts and resolve
                $conflicts = $this->detectConflicts($task, $user);
                if (!empty($conflicts)) {
                    $this->resolveConflicts($task, $conflicts);
                    $conflictsResolved += count($conflicts);
                }
            } catch (\Throwable $e) {
                Log::error('TaskScheduler: Recalculate failed for task ' . $task->id, [
                    'error' => $e->getMessage(),
                ]);
                $unscheduled++;
            }
        }

        return [
            'tasks_rescheduled' => $rescheduled,
            'tasks_unscheduled' => $unscheduled,
            'conflicts_resolved' => $conflictsResolved,
        ];
    }

    /**
     * Get suggested time blocks for a user on a specific date.
     *
     * @return array{available: array, busy: array, suggested_slots: array}
     */
    public function getSuggestedSlots($user, string $date): array
    {
        $workStart = $this->getUserWorkStart($user, now());
        $workEnd = $this->getUserWorkEnd($user, now());

        $dateStart = now()->parse($date)->startOfDay();
        $dateEnd = now()->parse($date)->endOfDay();

        $existingTasks = Task::where('user_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereBetween('scheduled_for', [$dateStart, $dateEnd])
            ->orderBy('scheduled_for')
            ->get();

        $busySlots = $existingTasks->map(function ($task) {
            return [
                'start' => $task->scheduled_for,
                'end' => now()->parse($task->scheduled_for)->addMinutes($task->estimated_duration ?? 30),
                'task_id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
            ];
        });

        $availableBlocks = $this->findAvailableBlocks($workStart, $workEnd, $existingTasks);

        $suggestedSlots = [];
        foreach ($availableBlocks as $block) {
            if ($block['duration'] >= 30) {
                $suggestedSlots[] = [
                    'start' => $block['start'],
                    'end' => $block['end'],
                    'duration' => $block['duration'],
                    'quality_score' => $this->scoreTimeSlot($block, $dateStart),
                ];
            }
        }

        usort($suggestedSlots, function ($a, $b) {
            return $b['quality_score'] <=> $a['quality_score'];
        });

        return [
            'available' => $availableBlocks,
            'busy' => $busySlots,
            'suggested_slots' => array_slice($suggestedSlots, 0, 5),
            'date' => $date,
        ];
    }

    /**
     * Detect scheduling conflicts for a task.
     *
     * @return array<array{type: string, conflicting_task: Task, overlap_minutes: int}>
     */
    public function detectConflicts(Task $task, $user): array
    {
        if (!$task->scheduled_for) {
            return [];
        }

        $taskStart = now()->parse($task->scheduled_for);
        $taskEnd = $taskStart->copy()->addMinutes($task->estimated_duration ?? 30);

        $otherTasks = Task::where('user_id', $user->id)
            ->where('id', '!=', $task->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('scheduled_for', '!=', null)
            ->get();

        $conflicts = [];
        foreach ($otherTasks as $other) {
            $otherStart = now()->parse($other->scheduled_for);
            $otherEnd = $otherStart->copy()->addMinutes($other->estimated_duration ?? 30);

            if ($taskStart->lt($otherEnd) && $taskEnd->gt($otherStart)) {
                $overlap = $taskStart->copy()->min($otherEnd)->diffInMinutes($taskStart->copy()->max($otherStart));
                $conflicts[] = [
                    'type' => 'overlap',
                    'conflicting_task' => $other,
                    'overlap_minutes' => $overlap,
                ];
            }
        }

        return $conflicts;
    }

    /*
    |--------------------------------------------------------------------------
    | Private Helper Methods
    |--------------------------------------------------------------------------
    */

    private function getUserWorkStart($user, $now): \Carbon\Carbon
    {
        // Default: 9:00 AM on the user's timezone
        // Future: Could be user preference or timezone-based
        return $now->copy()->hour(9)->minute(0)->second(0);
    }

    private function getUserWorkEnd($user, $now): \Carbon\Carbon
    {
        // Default: 6:00 PM
        return $now->copy()->hour(18)->minute(0)->second(0);
    }

    /**
     * Find available time blocks between busy tasks within work hours.
     *
     * @return array<array{start: string, end: string, duration: int}>
     */
    private function findAvailableBlocks(\Carbon\Carbon $workStart, \Carbon\Carbon $workEnd, $existingTasks): array
    {
        $blocks = [];
        $currentTime = $workStart->copy();

        // Sort existing tasks by start time
        $sorted = $existingTasks->sortBy('scheduled_for');

        foreach ($sorted as $task) {
            $taskStart = now()->parse($task->scheduled_for);

            // Skip tasks that end before work start or start after work end
            if ($taskStart->gt($workEnd)) {
                break;
            }

            $taskDuration = $task->estimated_duration ?? 30;
            $taskEnd = $taskStart->copy()->addMinutes($taskDuration);

            // Check if there's a gap between current time and this task
            if ($currentTime->lt($taskStart)) {
                $gapStart = $currentTime->copy();
                $gapEnd = $taskStart->copy();
                $gapMinutes = $gapStart->diffInMinutes($gapEnd);

                if ($gapMinutes >= 15) { // Minimum 15-minute blocks
                    $blocks[] = [
                        'start' => $gapStart->toDateTimeString(),
                        'end' => $gapEnd->toDateTimeString(),
                        'duration' => $gapMinutes,
                    ];
                }
            }

            // Move current time past this task
            if ($taskEnd->gt($currentTime)) {
                $currentTime = $taskEnd;
            }
        }

        // Add remaining time after last task
        if ($currentTime->lt($workEnd)) {
            $remainingMinutes = $currentTime->diffInMinutes($workEnd);
            if ($remainingMinutes >= 15) {
                $blocks[] = [
                    'start' => $currentTime->toDateTimeString(),
                    'end' => $workEnd->toDateTimeString(),
                    'duration' => $remainingMinutes,
                ];
            }
        }

        return $blocks;
    }

    /**
     * Find the best available slot for a task.
     */
    private function findBestSlot(array $availableBlocks, Task $task, \Carbon\Carbon $now): ?array
    {
        $requiredMinutes = $task->estimated_duration ?? 30;

        // Filter blocks that can fit the task
        $suitableBlocks = array_filter($availableBlocks, function ($block) use ($requiredMinutes) {
            return $block['duration'] >= $requiredMinutes;
        });

        if (empty($suitableBlocks)) {
            return null;
        }

        // Sort by quality score (highest first)
        usort($suitableBlocks, function ($a, $b) use ($task, $now) {
            $scoreA = $this->scoreTimeSlot($a, $now->toDateString());
            $scoreB = $this->scoreTimeSlot($b, $now->toDateString());
            return $scoreB <=> $scoreA;
        });

        $bestBlock = $suitableBlocks[0];

        return [
            'start' => $bestBlock['start'],
            'end' => now()->parse($bestBlock['start'])->addMinutes($requiredMinutes)->toDateTimeString(),
            'duration' => $requiredMinutes,
        ];
    }

    /**
     * Score a time slot based on productivity patterns.
     */
    private function scoreTimeSlot(array $block, string $date): float
    {
        $score = 50.0; // Base score
        $startTime = now()->parse($block['start']);
        $hour = (int) $startTime->format('H');

        // Morning bonus (9-11 AM = peak productivity)
        if ($hour >= 9 && $hour <= 11) {
            $score += 30;
        } elseif ($hour >= 14 && $hour <= 16) {
            // Afternoon moderate productivity
            $score += 15;
        } elseif ($hour >= 8 && $hour <= 17) {
            // Within work hours
            $score += 5;
        }

        // Block size bonus (larger blocks = more flexible)
        if ($block['duration'] >= 120) {
            $score += 15;
        } elseif ($block['duration'] >= 60) {
            $score += 10;
        } elseif ($block['duration'] >= 30) {
            $score += 5;
        }

        // Proximity to now (soonest first gets slight bonus)
        $now = now();
        $hoursUntil = $now->diffInHours($startTime, false);
        if ($hoursUntil >= 0 && $hoursUntil <= 4) {
            $score += 5;
        }

        return min($score, 100);
    }

    private function calculateConfidence(array $slot, Task $task, $existingTasks): float
    {
        $confidence = 70.0; // Base confidence

        // Higher confidence if slot matches peak hours
        $startTime = now()->parse($slot['start']);
        $hour = (int) $startTime->format('H');
        if ($hour >= 9 && $hour <= 11) {
            $confidence += 15;
        }

        // Higher confidence if task has clear duration
        if ($task->estimated_duration && $task->estimated_duration > 0) {
            $confidence += 10;
        }

        // Lower confidence if user has many tasks that day
        $taskCountToday = Task::where('user_id', $task->user_id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereDate('scheduled_for', $startTime->toDateString())
            ->count();

        if ($taskCountToday > 8) {
            $confidence -= 15;
        } elseif ($taskCountToday > 5) {
            $confidence -= 5;
        }

        return max(0, min(100, $confidence));
    }

    private function generateReason(array $slot, Task $task, $existingTasks): string
    {
        $startTime = now()->parse($slot['start']);
        $hour = (int) $startTime->format('H');
        $reason = "Scheduled at " . $startTime->format('g:i A');

        if ($hour >= 9 && $hour <= 11) {
            $reason .= " — peak productivity window";
        }

        if ($task->priority >= 4) {
            $reason .= " — prioritized as high priority";
        }

        // Check for dependencies
        if ($task->metadata['schedule_reason'] ?? null) {
            $reason = $task->metadata['schedule_reason'];
        }

        return $reason;
    }

    private function resolveConflicts(Task $task, array $conflicts): void
    {
        foreach ($conflicts as $conflict) {
            $other = $conflict['conflicting_task'];
            // Reschedule the lower priority task
            if ($task->priority > ($other->priority ?? 3)) {
                // Move the other task to next available slot
                $newSchedule = $this->schedule($other, $other->user);
                if ($newSchedule['scheduled_for']) {
                    $other->update(['scheduled_for' => $newSchedule['scheduled_for']]);
                    Log::info('TaskScheduler: Auto-rescheduled task ' . $other->id . ' due to conflict');
                }
            }
        }
    }
}
