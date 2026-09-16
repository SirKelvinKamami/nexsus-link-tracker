<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Task Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #2563eb;">Task Reminder</h2>
    <p>You have a task reminder:</p>
    <div style="background: #f3f4f6; padding: 16px; border-radius: 8px; margin: 16px 0;">
        <h3 style="margin-top: 0;">{{ $task ? $task->title : 'Untitled Task' }}</h3>
        @if($task && $task->description)
            <p>{{ $task->description }}</p>
        @endif
        @if($task && $task->due_datetime)
            <p><strong>Due:</strong> {{ $task->due_datetime->format('M d, Y H:i') }}</p>
        @endif
        @if($task && $task->priority)
            <p><strong>Priority:</strong> {{ ['1' => 'Low', '2' => 'Below Normal', '3' => 'Normal', '4' => 'High', '5' => 'Critical'][$task->priority] ?? 'Normal' }}</p>
        @endif
        <p><strong>Channel:</strong> {{ $reminder->channel ?? 'N/A' }}</p>
    </div>
    <p><a href="{{ url('/studio/tasks') }}" style="background: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">View Task</a></p>
    <p style="color: #6b7280; font-size: 12px;">This is an automated reminder from Nexsus Link Tracker.</p>
</body>
</html>