<?php

namespace App\Services;

use App\Models\Reminder;
use App\Models\ReminderLog;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ReminderEngine
{
    /**
     * Create a reminder for a task.
     *
     * @return Reminder
     */
    public function createReminder(array $data): Reminder
    {
        $reminder = Reminder::create($data);

        $this->recordLog($reminder, 'created', ['metadata' => $data]);

        return $reminder;
    }

    /**
     * Send a reminder immediately via its configured channel.
     */
    public function sendReminder(Reminder $reminder): bool
    {
        try {
            if ($reminder->sent) {
                Log::info('ReminderEngine: Reminder ' . $reminder->id . ' already sent');
                return false;
            }

            $channel = $reminder->channel;
            $sent = false;

            switch ($channel) {
                case 'email':
                    $sent = $this->sendEmail($reminder);
                    break;
                case 'desktop':
                    $sent = $this->sendDesktop($reminder);
                    break;
                case 'push':
                    $sent = $this->sendPush($reminder);
                    break;
                default:
                    Log::warning('ReminderEngine: Unknown channel ' . $channel);
                    return false;
            }

            if ($sent) {
                $reminder->update([
                    'sent' => true,
                    'sent_at' => now(),
                ]);
                $this->recordLog($reminder, 'sent', ['channel' => $channel]);
                Log::info('ReminderEngine: Reminder ' . $reminder->id . ' sent via ' . $channel);
            }

            return $sent;
        } catch (\Throwable $e) {
            Log::error('ReminderEngine: Send failed for reminder ' . $reminder->id, [
                'error' => $e->getMessage(),
            ]);
            $this->recordLog($reminder, 'failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get all reminders that are due (not yet sent, trigger time reached).
     */
    public function getDueReminders()
    {
        return Reminder::pending()
            ->where('trigger_value', '<=', now())
            ->get();
    }

    /**
     * Process all due reminders: send each one.
     */
    public function processDueReminders(): int
    {
        $due = $this->getDueReminders();
        $sent = 0;

        foreach ($due as $reminder) {
            if ($this->sendReminder($reminder)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Record a user response to a reminder.
     */
    public function respondToReminder(Reminder $reminder, string $response): bool
    {
        $validResponses = ['dismissed', 'snoozed', 'acknowledged', 'completed'];
        if (!in_array($response, $validResponses)) {
            return false;
        }

        $reminder->update([
            'user_response' => $response,
            'responded_at' => now(),
        ]);

        $this->recordLog($reminder, 'responded_' . $response, []);

        // If completed, also complete the linked task
        if ($response === 'completed' && $reminder->task) {
            $reminder->task->update(['status' => 'completed']);
            $this->recordLog($reminder, 'task_completed', ['task_id' => $reminder->task->id]);
        }

        return true;
    }

    /**
     * Record a log entry for a reminder action.
     *
     * @return ReminderLog
     */
    public function recordLog(Reminder $reminder, string $action, array $metadata = []): ReminderLog
    {
        return ReminderLog::create([
            'reminder_id' => $reminder->id,
            'action' => $action,
            'metadata' => $metadata ?: null,
        ]);
    }

    /**
     * Get reminders for a specific user, paginated.
     */
    public function getUserReminders($userId, int $perPage = 50)
    {
        return Reminder::where('user_id', $userId)
            ->with('task')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get a single reminder by ID for a user.
     */
    public function getUserReminder($reminderId, $userId): ?Reminder
    {
        return Reminder::where('id', $reminderId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Auto-check and process reminders due within the next period.
     * Called by scheduler or cron.
     */
    public function autoCheck(): array
    {
        $due = $this->getDueReminders();
        $results = [
            'total_due' => $due->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($due as $reminder) {
            if ($this->sendReminder($reminder)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /*
    |--------------------------------------------------------------------------
    | Private Channel Methods
    |--------------------------------------------------------------------------
    */

    private function sendEmail(Reminder $reminder): bool
    {
        try {
            $task = $reminder->task;
            $user = $task ? $task->user : null;

            if (!$user || !$user->email) {
                Log::warning('ReminderEngine: No user email for reminder ' . $reminder->id);
                return false;
            }

            $taskTitle = $task ? $task->title : 'Untitled Task';
            $taskLink = $task ? url('/studio/tasks') : '#';

            Mail::to($user->email)->send(new \App\Mail\TaskReminderMail($reminder, $task));

            return true;
        } catch (\Throwable $e) {
            Log::error('ReminderEngine: Email send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function sendDesktop(Reminder $reminder): bool
    {
        Log::info('ReminderEngine: Desktop notification for reminder ' . $reminder->id, [
            'task' => $reminder->task ? $reminder->task->title : 'N/A',
            'channel' => 'desktop',
        ]);
        return true;
    }

    private function sendPush(Reminder $reminder): bool
    {
        Log::info('ReminderEngine: Push notification for reminder ' . $reminder->id, [
            'task' => $reminder->task ? $reminder->task->title : 'N/A',
            'channel' => 'push',
        ]);
        return true;
    }
}