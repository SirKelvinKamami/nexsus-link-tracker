<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Document Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for document uploads in the link tracker.
    |
    */

    'storage_disk' => env('DOCUMENT_STORAGE_DISK', 'local'),

    'storage_path' => 'documents',

    'max_file_size' => env('DOCUMENT_MAX_SIZE', 10240), // KB (10MB)

    'allowed_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/zip',
        'application/x-zip-compressed',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ],

    'allowed_extensions' => [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip',
        'jpg', 'jpeg', 'png', 'gif', 'webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Preview Settings
    |--------------------------------------------------------------------------
    |
    | Settings for document preview on the bio page.
    |
    */

    'preview' => [
        'image_thumb_width' => 300,
        'image_thumb_height' => 200,
        'pdf_embed_url' => 'https://docs.google.com/gview?url=',
    ],

    /*
    |--------------------------------------------------------------------------
    | Download Tracking
    |--------------------------------------------------------------------------
    |
    | Settings for tracking document downloads.
    |
    */

    'tracking' => [
        'enabled' => env('DOCUMENT_TRACKING_ENABLED', true),
        'increment_click_number' => true,
    ],

];
