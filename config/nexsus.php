<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Single User Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, only one user can register. The first user becomes the
    | admin. Set to 'true', 'false', or 'auth' in your .env file.
    |
    */

    'single_user_mode' => in_array(
        strtolower((string) env('SINGLE_USER_MODE', 'false')),
        ['true', '1', 'yes', 'auth'],
        true
    ),

    /*
    |--------------------------------------------------------------------------
    | User Cap
    |--------------------------------------------------------------------------
    |
    | Maximum number of users allowed. Set to 0 for unlimited.
    |
    */

    'user_cap' => env('USER_CAP', 0),

    /*
    |--------------------------------------------------------------------------
    | Random User IDs
    |--------------------------------------------------------------------------
    |
    | When disabled, sequential integer IDs are used instead of random IDs.
    |
    */

    'disable_random_user_ids' => env('DISABLE_RANDOM_USER_IDS', false),
    'user_id_length' => env('USER_ID_LENGTH', 9),

    /*
    |--------------------------------------------------------------------------
    | Random Link IDs
    |--------------------------------------------------------------------------
    |
    | When disabled, sequential integer IDs are used instead of random IDs.
    |
    */

    'disable_random_link_ids' => env('DISABLE_RANDOM_LINK_IDS', false),
    'link_id_length' => env('LINK_ID_LENGTH', 9),

];
