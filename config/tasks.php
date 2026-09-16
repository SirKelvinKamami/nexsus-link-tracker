<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Task Scheduler Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the AI Task Scheduler & Reminder module.
    | Integrates with nexsus-link-tracker's existing infrastructure.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Task ID Length
    |--------------------------------------------------------------------------
    |
    | Number of digits for auto-generated task IDs (matching User/Link patterns).
    |
    */
    'task_id_length' => 9,

    /*
    |--------------------------------------------------------------------------
    | Disable Random Task IDs
    |--------------------------------------------------------------------------
    |
    | Set to 'true' to use auto-incrementing IDs instead of random 9-digit IDs.
    |
    */
    'disable_random_task_ids' => 'false',

    /*
    |--------------------------------------------------------------------------
    | Default Priority
    |--------------------------------------------------------------------------
    |
    | Default priority for new tasks (1=low, 5=high).
    |
    */
    'default_priority' => 3,

    /*
    |--------------------------------------------------------------------------
    | Priority Labels
    |--------------------------------------------------------------------------
    |
    | Mapping of priority numbers to labels.
    |
    */
    'priority_labels' => [
        1 => 'Low',
        2 => 'Below Normal',
        3 => 'Normal',
        4 => 'High',
        5 => 'Critical',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Values
    |--------------------------------------------------------------------------
    |
    | Valid task status values.
    |
    */
    'statuses' => ['pending', 'in_progress', 'completed', 'cancelled', 'snoozed'],

    /*
    |--------------------------------------------------------------------------
    | Default Status
    |--------------------------------------------------------------------------
    |
    | Status assigned to newly created tasks.
    |
    */
    'default_status' => 'pending',

    /*
    |--------------------------------------------------------------------------
    | AI Parser
    |--------------------------------------------------------------------------
    |
    | Configuration for the NLP task parsing service (Phase 4.1).
    | Uses dual providers: OpenAI (primary) + Anthropic (fallback).
    |
    */
    'ai_parser' => [
        'enabled' => false,
        'primary' => [
            'provider' => 'openai',
            'api_key' => env('OPENAI_API_KEY', null),
            'model' => 'gpt-4o-mini',
            'timeout' => 30,
        ],
        'fallback' => [
            'provider' => 'anthropic',
            'api_key' => env('ANTHROPIC_API_KEY', null),
            'model' => 'claude-3-haiku-4-20250901',
            'timeout' => 30,
        ],
        'cache_results' => true,
        'cache_ttl' => 86400, // 24 hours
        'retry_on_failure' => true,
        'max_retries' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    |
    | Configuration for the smart scheduling engine (Phase 4.2).
    |
    */
    'scheduling' => [
        'enabled' => false,
        'default_block_minutes' => 30,
        'max_daily_tasks' => 12,
        'respect_energy_patterns' => false,
        'calendar_integration' => false,
        'calendar_provider' => null, // google, outlook, none
    ],

    /*
    |--------------------------------------------------------------------------
    | Reminders
    |--------------------------------------------------------------------------
    |
    | Configuration for the reminder engine (Phase 4.3).
    |
    */
    'reminders' => [
        'enabled' => true,
        'channels' => ['email', 'desktop', 'push'],
        'email' => [
            'enabled' => true,
            'template' => 'emails.task-reminder',
            'send_at' => '-15 minutes',
        ],
        'desktop' => [
            'enabled' => true,
            'sound' => true,
        ],
        'push' => [
            'enabled' => true,
        ],
        'lead_times' => [
            'enabled' => true,
            'minutes_before' => [15, 60, 1440],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Digest
    |--------------------------------------------------------------------------
    |
    | Configuration for the daily digest notification (Phase 4.4).
    |
    */
    'daily_digest' => [
        'enabled' => true,
        'time' => '08:00',
        'channel' => 'email', // email, desktop, both
        'include_overdue' => true,
        'include_completed' => true,
        'include_suggestions' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-User Limits
    |--------------------------------------------------------------------------
    |
    | Task limits per user.
    |
    */
    'limits' => [
        'max_tasks_per_user' => 1000,
        'max_subtasks_per_task' => 50,
        'max_items_per_task' => 100,
    ],
];
