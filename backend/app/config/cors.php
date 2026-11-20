<?php

/**
 * MASAR CORS Configuration
 *
 * Compatibility layer: the authoritative source of truth for CORS settings is
 * the central application configuration (`app.php` -> `cors`). This file remains
 * so existing requires keep working and simply forwards to the central array,
 * falling back to safe development defaults when the central config is absent.

 */

global $app_config;

return $app_config['cors'] ?? [
    'enabled' => true,
    'allowed_origins' => [ 'http://localhost:3000', 'http://127.0.0.1:3000', ],
    'allowed_methods' => [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', ],
    'allowed_headers' => [ 'Content-Type', 'Authorization', 'Accept', 'Origin', 'X-Requested-With', 'X-CSRF-Token', ],
    'exposed_headers' => [ 'Content-Length', 'Content-Type', ],
    'allow_credentials' => true,
    'max_age' => 86400,
];
