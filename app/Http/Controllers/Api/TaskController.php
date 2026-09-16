<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskItem;
use App\Models\ScheduleRule;
use App\Services\AiParser;
use App\Services\ProductivityTracker;
use App\Services\TaskScheduler;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * List tasks.
     */
    public function index(Request $request)
    {
        try {
            $userId = auth()->id();
            $query = Task::forUser($userId);

            if ($request->has('status')) {
                $query->where('status', $request->query('status'));
            }
            if ($request->has('priority')) {
                $query->where('priority', $request->query('priority'));
            }
            if ($request->has('project')) {
                $query->where('project', $request->query('project'));
            }
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->query('search') . '%');
            }

            $tasks = $query->orderByDesc('priority')->orderBy('due_datetime')->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $tasks,
                'meta' => [
                    'total' => $tasks->total(),
                    'per_page' => $tasks->perPage(),
                    'current_page' => $tasks->currentPage(),
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch tasks',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single task.
     */
    public function show($id)
    {
        try {
            $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $task->load('items', 'parent', 'subtasks');

            return response()->json([
                'success' => true,
                'data' => $task,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Task not found',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create task.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'priority' => 'nullable|integer|between:1,5',
                'due_datetime' => 'nullable|date|date_format:Y-m-d H:i:s',
                'estimated_duration' => 'nullable|integer|min:1',
                'project' => 'nullable|string|max:255',
                'parent_task_id' => 'nullable|exists:tasks,id',
                'metadata' => 'nullable|array',
            ]);

            $validated['user_id'] = auth()->id();
            $validated['status'] = $validated['status'] ?? 'pending';

            $task = Task::create($validated);

            return response()->json([
                'success' => true,
                'data' => $task,
            ], 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create task',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update task.
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:pending,in_progress,completed,cancelled,snoozed',
                'priority' => 'sometimes|required|integer|between:1,5',
                'due_datetime' => 'nullable|date|date_format:Y-m-d H:i:s',
                'estimated_duration' => 'nullable|integer|min:1',
                'project' => 'nullable|string|max:255',
                'metadata' => 'nullable|array',
            ]);

            $task->update($validated);
            $task->refresh();

            return response()->json([
                'success' => true,
                'data' => $task,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to update task',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete task.
     */
    public function destroy($id)
    {
        try {
            $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete task',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete task.
     */
    public function complete($id)
    {
        try {
            $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $task->update(['status' => 'completed']);

            // Auto-complete all subtasks
            foreach ($task->subtasks as $subtask) {
                $subtask->update(['status' => 'completed']);
            }

            // Auto-complete all items
            foreach ($task->items as $item) {
                $item->complete();
            }

            $task->refresh();

            return response()->json([
                'success' => true,
                'data' => $task,
                'message' => 'Task completed',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to complete task',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse natural language into task structure.
     */
    public function parse(Request $request, AiParser $parser)
    {
        try {
            $validated = $request->validate([
                'text' => 'required|string|max:1000',
            ]);

            $result = $parser->parse($validated['text']);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to parse task',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get daily digest for current user — aligned with Figma prototype.
     */
    public function dailyDigest()
    {
        try {
            $userId = auth()->id();
            $today = now()->startOfDay();
            $tomorrow = now()->addDay()->startOfDay();
            $yesterday = now()->copy()->subDay();

            $tracker = new ProductivityTracker();
            $yesterdayReview = $tracker->yesterdayReview($userId);

            // Today's tasks as timeline
            $dueToday = Task::forUser($userId)
                ->whereBetween('due_datetime', [$today, $tomorrow])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->orderBy('due_datetime')
                ->get()
                ->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'priority' => $task->priority,
                        'due_datetime' => $task->due_datetime?->format('H:i'),
                        'project' => $task->project,
                        'estimated_duration' => $task->estimated_duration,
                        'status' => $task->status,
                    ];
                });

            $productivity = $tracker->productivityScore($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => now()->toDateString(),
                    'greeting' => 'Good ' . ($now = now()->hour < 12 ? 'morning' : ($now->hour < 18 ? 'afternoon' : 'evening')) . ', User',
                    'summary' => [
                        'total_active' => $dueToday->count(),
                        'high_priority' => Task::forUser($userId)->where('priority', '>=', 4)->whereNotIn('status', ['completed', 'cancelled'])->count(),
                    ],
                    'today_plan' => [
                        'time_slots' => $dueToday,
                    ],
                    'yesterday_review' => [
                        'completed' => $yesterdayReview['completed_display'],
                        'time_spent_hours' => $yesterdayReview['time_spent_hours'],
                        'overdue' => $yesterdayReview['overdue'],
                        'rate' => $yesterdayReview['rate'],
                    ],
                    'ai_insights' => [
                        'icon' => '✦',
                        'items' => $productivity['insights'],
                    ],
                    'actions' => [
                        ['label' => 'Got it — Let\'s go!', 'type' => 'primary'],
                        ['label' => 'Snooze 1 hour', 'type' => 'outline'],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate digest',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Task analytics overview.
     */
    public function analyticsOverview(Request $request, ProductivityTracker $tracker)
    {
        try {
            $userId = auth()->id();
            $period = $request->input('period', '30d');

            return $this->success($tracker->overview($userId, $period));
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch analytics', $e->getMessage(), 500);
        }
    }

    /**
     * Completion trend data for line chart.
     */
    public function completionTrend(Request $request, ProductivityTracker $tracker)
    {
        try {
            $userId = auth()->id();
            $days = (int) $request->input('days', 7);

            return $this->success($tracker->completionTrend($userId, min($days, 90)));
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch completion trend', $e->getMessage(), 500);
        }
    }

    /**
     * Priority distribution for donut chart.
     */
    public function priorityDistribution(ProductivityTracker $tracker)
    {
        try {
            $userId = auth()->id();

            return $this->success($tracker->priorityDistribution($userId));
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch priority distribution', $e->getMessage(), 500);
        }
    }

    /**
     * Productivity score with factors and insights.
     */
    public function productivityScore(ProductivityTracker $tracker)
    {
        try {
            $userId = auth()->id();
            $score = $tracker->productivityScore($userId);
            $score['focus_time_hours'] = $tracker->focusTime($userId);

            return $this->success($score);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch productivity score', $e->getMessage(), 500);
        }
    }

    /**
     * Top completed tasks table.
     */
    public function topCompleted(ProductivityTracker $tracker)
    {
        try {
            $userId = auth()->id();
            $limit = (int) request()->input('limit', 5);

            return $this->success($tracker->topCompletedTasks($userId, $limit));
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch top tasks', $e->getMessage(), 500);
        }
    }

    /**
     * Get suggested time slots for a date.
     */
    public function schedule(Request $request, TaskScheduler $scheduler)
    {
        try {
            $date = $request->input('date', now()->toDateString());
            $result = $scheduler->getSuggestedSlots(auth()->user(), $date);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to get schedule',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Schedule a single task.
     */
    public function scheduleTask(Request $request, $id, TaskScheduler $scheduler)
    {
        try {
            $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $result = $scheduler->schedule($task, auth()->user());

            if ($result['scheduled_for']) {
                $task->update([
                    'scheduled_for' => $result['scheduled_for'],
                    'metadata' => array_merge($task->metadata ?? [], [
                        'schedule_confidence' => $result['confidence'],
                        'schedule_reason' => $result['reason'],
                    ]),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => array_merge($task, ['schedule_result' => $result]),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to schedule task',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate all pending tasks.
     */
    public function recalculate(Request $request, TaskScheduler $scheduler)
    {
        try {
            $result = $scheduler->recalculateAll(auth()->user());

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to recalculate schedule',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get schedule rules for user.
     */
    public function getRules(Request $request)
    {
        try {
            $rules = ScheduleRule::forUser(auth()->id());

            return response()->json([
                'success' => true,
                'data' => $rules,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch rules',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a schedule rule.
     */
    public function createRule(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'rule_type' => 'required|in:recurring,energy_pattern,priority_auto,focus_block',
                'rule_config' => 'required|array',
            ]);

            $rule = ScheduleRule::create([
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'rule_type' => $validated['rule_type'],
                'rule_config' => $validated['rule_config'],
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'data' => $rule,
            ], 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create rule',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
