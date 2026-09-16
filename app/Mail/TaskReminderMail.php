<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reminder;
    public $task;

    public function __construct($reminder, $task)
    {
        $this->reminder = $reminder;
        $this->task = $task;
    }

    public function build()
    {
        return $this->view('emails.task-reminder')
                    ->subject('Task Reminder: ' . ($this->task ? $this->task->title : 'Untitled Task'));
    }
}