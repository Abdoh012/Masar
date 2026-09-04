<?php

return [
    'name'              => 'MASAR',
    'environment'       => getenv('APP_ENV') ?: 'development',
    'debug'             => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
    'url'               => getenv('APP_URL') ?: 'http://localhost',
    'api_version'       => 'v1',
    'api_prefix'        => '/api/v1',
    'timezone'          => getenv('APP_TIMEZONE') ?: 'Africa/Cairo',
    'locale'            => 'en',
    'supported_locales' => ['en'],
    'date_format'       => 'Y-m-d',
    'datetime_format'   => 'Y-m-d H:i:s',
    'pagination'        => ['default_per_page' => 20, 'max_per_page' => 100,],
    'password'          => ['algorithm' => PASSWORD_DEFAULT,],
    'session'           => ['name' => 'MASAR_SESSION',],
    'auth'              => [
        'token_expiration'   => 60 * 60 * 24 * 7,   // 7 days
        'remember_expiration'=> 60 * 60 * 24 * 30,  // 30 days
        'cookie_name'        => 'MASAR_REMEMBER',
        'cookie_path'        => '/',
        'cookie_secure'      => false,
        'cookie_httponly'    => true,
        'cookie_samesite'    => 'Lax',
    ],
    'response'          => ['success_key' => 'success','message_key' => 'message','data_key'    => 'data','errors_key'  => 'errors',],
    'cors' => [
        'allowed_origins' => array_filter(array_map('trim', preg_split('/\s*,\s*/', (string) (getenv('CORS_ALLOWED_ORIGINS') ?: 'http://localhost:3000,http://localhost:5173')))),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-CSRF-Token', 'X-Requested-With'],
        'allow_credentials' => true,
        'max_age' => 86400,
        'development' => filter_var(getenv('APP_ENV') ?: 'development', FILTER_VALIDATE_BOOLEAN) === false,
    ],
];
