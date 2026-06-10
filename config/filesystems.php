<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        'sftp_delivery' => [
            'driver'          => 'sftp',
            'host'            => env('SFTP_DELIVERY_HOST'),
            'username'        => env('SFTP_DELIVERY_USERNAME'),
            'password'        => env('SFTP_DELIVERY_PASSWORD'),
            'port'            => (int) env('SFTP_PORT', 22),
            'root'            => env('SFTP_DELIVERY_ROOT', '/'),
            'visibility'      => 'public',
            'directory_visibility' => 'public',
            'permissions' => [
                'file' => [
                    'public' => 0666,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0700,
                ],
            ],
            'timeout'         => 30, 
            'connectTimeout'  => 30,
            'hostFingerprint' => null, 
            'useAgent'        => false, // 🚀 PASSWORD-FIRST (v97 Fix)
        ],

        'google_drive' => [
            'driver' => 'google',
            'clientId' => env('GOOGLE_CLIENT_ID'),
            'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
            'refreshToken' => env('GOOGLE_REFRESH_TOKEN'),
            'folderId' => env('GOOGLE_DRIVE_DELIVERY_FOLDER_ID'),
            'serviceAccountJson' => storage_path('app/google-service-account.json'),
        ],

        'nitro' => [
            'driver' => 'local',
            'root' => env('NITRO_STORAGE_ROOT', 'C:/DataPortal_Nitro_Storage'),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
