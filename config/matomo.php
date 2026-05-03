<?php

return [
    /*
     * The Matomo Server URL.
     * Example: https://your-matomo-domain.com
     */
    'url' => env('MATOMO_URL', ''),

    /*
     * The Matomo Site ID.
     */
    'site_id' => env('MATOMO_SITE_ID', 1),

    /*
     * The Matomo Auth Token.
     * Required for manual IP tracking or administrative features.
     */
    'token' => env('MATOMO_TOKEN', ''),

    /*
     * The user attribute to be used as the User ID in Matomo.
     * Default is 'email'. You can change this to 'id', 'username', etc.
     */
    'user_id_attribute' => env('MATOMO_USER_ID_ATTRIBUTE', 'email'),

    /*
     * If enabled, tracking requests will be dispatched to the Laravel Queue.
     * This is highly recommended for high-traffic applications.
     */
    'queue' => env('MATOMO_QUEUE', false),
];