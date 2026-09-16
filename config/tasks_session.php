<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Task Scheduler — Session Rules
    |--------------------------------------------------------------------------
    | Project: nexsus-link-tracker
    | Module: AI Task Scheduler & Reminder
    | Phase: 4.0 In Progress
    | Last Updated: 2026-09-16
    | Authority: SirKelvin Kamami (Boss)
    |--------------------------------------------------------------------------
    |
    | These rules supplement the existing AGENTS.md.
    | Task scheduler development follows the same conventions as the core project.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Session Start (MANDATORY)
    |--------------------------------------------------------------------------
    */
    # 1. Read existing AGENTS.md for core project status
    # 2. Read this file for task scheduler context
    # 3. Check git status — uncommitted changes
    # 4. Check .env — config is correct (DB_CONNECTION, API_TOKEN)
    # 5. Check server: curl http://127.0.0.1:8000/

    /*
    |--------------------------------------------------------------------------
    | Session End (MANDATORY)
    |--------------------------------------------------------------------------
    */
    # 1. Update this file if phases changed
    # 2. Run php artisan migrate:status — verify migrations
    # 3. Run php artisan route:list — verify routes
    # 4. Test API: curl -H "Authorization: Bearer {TOKEN}" http://127.0.0.1:8000/api/v1/tasks
    # 5. Commit with descriptive message
    # 6. Push to origin/main

    /*
    |--------------------------------------------------------------------------
    | Task Scheduler Rules
    |--------------------------------------------------------------------------
    */
    rules: [
        # Naming
        'task_models' => 'app/Models/Task.php, app/Models/TaskItem.php',
        'task_controller' => 'app/Http/Controllers/Api/TaskController.php',
        'task_migrations' => 'database/migrations/2026_09_16_*_create_tasks_table.php',

        # API format — MUST follow existing pattern
        'response_format' => '{ "success": bool, "data": ..., "error": ... }',
        'api_prefix' => '/api/v1',
        'api_auth' => 'Bearer token (api.token middleware)',

        # Database rules
        'foreign_keys' => 'Use $table->foreign()->references()->on()->onDelete()',
        'cascade_delete' => 'TaskItems cascade on Task delete',
        'no_expose_raw' => 'Never expose raw user input in queries',
        'fillable' => 'Always use $fillable on models, never $guarded',

        # Scheduling
        'use_artisan_scheduler' => 'Use php artisan schedule:run for reminders',
        'queue_jobs' => 'Use Laravel queues for async operations',

        # Frontend
        'views_location' => 'resources/views/studio/',
        'use_blade' => 'Blade templates with @extends layouts',
        'tailwind' => 'Use Tailwind CSS classes',

        # Error handling
        'try_catch_required' => 'Wrap all controller methods in try/catch',
        'report_errors' => 'Use report($e) for exceptions',
    ],
];
