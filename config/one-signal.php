<?php
return [

    /*
    |-------------------------------------------------------------------------------------------
    | URL - One Signal have different endpoint.
    |-------------------------------------------------------------------------------------------
    |
     */
    'url' => env('ONE_SIGNAL_URL', 'https://onesignal.com/api/v1/'),
    'is_local' => env('IS_LOCAL', false),
    'is_prod' => env('IS_PROD', false),
    /*
    |-------------------------------------------------------------------------------------------
    | App Id - One Signal have different app id for every app.
    |
    | Based on App you are using, you can change the App Id here and specify here
    |-------------------------------------------------------------------------------------------
    |
     */
    'app_id_driver' => env('ONE_SIGNAL_APP_ID_DRIVER'),
    'app_id_worker' => env('ONE_SIGNAL_APP_ID_WORKER'),
    'app_id_business' => env('ONE_SIGNAL_APP_ID_BUSINESS'),
    'app_id_web' => env('ONE_SIGNAL_APP_ID_WEB'),
    'app_id_web_local' => env('ONE_SIGNAL_APP_ID_WEB_LOCAL'),
    'app_id_web_prod' => env('ONE_SIGNAL_APP_ID_WEB_PROD'),

    /*
    |-------------------------------------------------------------------------------------------
    | Authorize - One Signal have different Authorize for every app.
    |
    | Based on App you are using, you can change the Authorize here and specify here
    |-------------------------------------------------------------------------------------------
    |
     */
    'authorize_driver' => env('ONE_SIGNAL_AUTHORIZE_DRIVER'),
    'authorize_worker' => env('ONE_SIGNAL_AUTHORIZE_WORKER'),
    'authorize_business' => env('ONE_SIGNAL_AUTHORIZE_BUSINESS'),
    'authorize_web' => env('ONE_SIGNAL_AUTHORIZE_WEB'),
    'authorize_web_local' => env('ONE_SIGNAL_AUTHORIZE_WEB_LOCAL'),
    'authorize_web_prod' => env('ONE_SIGNAL_AUTHORIZE_WEB_PROD'),
    /*
    |-------------------------------------------------------------------------------------------
    | mutable_content - Always defaults to true and cannot be turned off. Allows tracking of notification receives
    | and changing of the notification content in app before it is displayed.
    |-------------------------------------------------------------------------------------------
    |
     */
    'mutable_content' => env('ONE_SIGNAL_MUTABLE_CONTENT', true),

    /*
    |-------------------------------------------------------------------------------------------
    | Auth Key - One Signal have Auth key of account.
    |
    | You can manage apps
    |-------------------------------------------------------------------------------------------
    |
     */
    'auth_key' => env('ONE_SIGNAL_AUTH_KEY'),
];
