<?php

/**
 * Handles the logic for "task-dashboard" block type.
 *
 * @param \Illuminate\Http\Request $request
 * @param mixed $linkType
 * @return array
 */
function handleTaskDashboardType($request, $linkType)
{
    $rules = [
        'show_stats' => 'nullable|in:yes,no',
        'stat_count' => 'nullable|integer|min:2|max:6',
        'show_overdue' => 'nullable|in:yes,no',
        'show_completed' => 'nullable|in:yes,no',
    ];

    $linkData = [
        'show_stats' => $request->input('show_stats', 'yes'),
        'stat_count' => $request->input('stat_count', 4),
        'show_overdue' => $request->input('show_overdue', 'yes'),
        'show_completed' => $request->input('show_completed', 'yes'),
    ];

    return ['rules' => $rules, 'linkData' => $linkData];
}