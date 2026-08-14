<?php

/**
 * MASAR CORS Configuration
 *
 * This file contains Cross-Origin Resource Sharing settings
 * used by the MASAR API.
 */

return [
    'enabled' => true,

    'allowed_origins' => [ 'http://localhost:3000', 'http://127.0.0.1:3000', ],

    'allowed_methods' => [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS' ],

    'allowed_headers' => [ 'Content-Type', 'Authorization', 'Accept', 'Origin', 'X-Requested-With' ],

    'exposed_headers' => [ 'Content-Length', 'Content-Type' ],

    'allow_credentials' => true,

    'max_age' => 86400,

];