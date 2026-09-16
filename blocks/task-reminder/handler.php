<?php

/**
 * Handles the logic for "task-reminder" block type.
 *
 * @param \Illuminate\Http\Request $request
 * @param mixed $linkType
 * @return array
 */
function handleTaskReminderType($request, $linkType)
{
    $rules = [
        'display_type' => 'required|in:tasks,reminders,both',
        'max_items' => 'nullable|integer|min:1|max:50',
        'show_priority' => 'nullable|in:yes,no',
        'show_due_date' => 'nullable|in:yes,no',
        'show_project' => 'nullable|in:yes,no',
        'status_filter' => 'nullable|string',
        'priority_filter' => 'nullable|integer|between:1,5',
    ];

    $linkData = [
        'display_type' => $request->input('display_type', 'tasks'),
        'max_items' => $request->input('max_items', 10),
        'show_priority' => $request->input('show_priority', 'yes'),
        'show_due_date' => $request->input('show_due_date', 'yes'),
        'show_project' => $request->input('show_project', 'yes'),
        'status_filter' => $request->input('status_filter', ''),
        'priority_filter' => $request->input('priority_filter', ''),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}