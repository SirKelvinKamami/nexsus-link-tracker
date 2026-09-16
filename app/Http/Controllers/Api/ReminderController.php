<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Reminder;
use App\Services\ReminderEngine;
use Illuminate\Http\Request;

class ReminderController extends BaseController
{
    /**
     * List reminders for the authenticated user.
     */
    public function index(Request $request, ReminderEngine $engine)
    {
        try {
            $userId = $this->getUserId($request);
            $reminders = $engine->getUserReminders($userId, $request->input('per_page', 50));

            return $this->success($reminders, [
                'total' => $reminders->total(),
                'per_page' => $reminders->perPage(),
                'current_page' => $reminders->currentPage(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to fetch reminders', $e->getMessage(), 500);
        }
    }

    /**
     * Create a reminder for a task.
     */
    public function store(Request $request, ReminderEngine $engine)
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id',
                'trigger_type' => 'required|in:time,dependency,progress,context',
                'trigger_value' => 'required|date',
                'channel' => 'required|in:email,desktop,push',
            ]);

            $validated['user_id'] = $this->getUserId($request);

            $reminder = $engine->createReminder($validated);

            return $this->success($reminder, [], 201);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to create reminder', $e->getMessage(), 422);
        }
    }

    /**
     * Send a reminder immediately.
     */
    public function send($id, ReminderEngine $engine)
    {
        try {
            $userId = $this->getUserId(request());
            $reminder = $engine->getUserReminder($id, $userId);

            if (!$reminder) {
                return $this->error('Reminder not found', 'Reminder does not exist or access denied', 404);
            }

            $sent = $engine->sendReminder($reminder);

            if ($sent) {
                return $this->success(null, [], 200);
            }

            return $this->error('Failed to send reminder', 'Reminder could not be sent', 500);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to send reminder', $e->getMessage(), 500);
        }
    }

    /**
     * Record user response to a reminder.
     */
    public function respond($id, Request $request, ReminderEngine $engine)
    {
        try {
            $userId = $this->getUserId($request);
            $reminder = $engine->getUserReminder($id, $userId);

            if (!$reminder) {
                return $this->error('Reminder not found', 'Reminder does not exist or access denied', 404);
            }

            $validated = $request->validate([
                'response' => 'required|in:dismissed,snoozed,acknowledged,completed',
            ]);

            $engine->respondToReminder($reminder, $validated['response']);

            return $this->success(null, [], 200);
        } catch (\Throwable $e) {
            report($e);
            return $this->error('Failed to record response', $e->getMessage(), 500);
        }
    }
}