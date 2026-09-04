<?php
return [
    'storage_path'       => getenv('UPLOAD_STORAGE_PATH') ?: __DIR__ . '/../../storage/uploads',

    'public_url'         => getenv('UPLOAD_PUBLIC_URL') ?: '/storage/uploads',

    'max_size'           => 10 * 1024 * 1024,

    'allowed_extensions' => [ 'pdf', 'doc', 'docx' ],

    'allowed_mimes'      => [ 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ],

    'cv'                 => [
        'directory'          => 'cvs',
        'max_size'           => 10 * 1024 * 1024,
        'allowed_extensions' => ['pdf','doc','docx'],
        'allowed_mimes'      => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
    ],

    'temp'               => [ 'directory' => 'temp', 'max_size'  => 20 * 1024 * 1024, ],

    'filename'           => [ 'use_original_name'    => false, 'generate_unique_name' => true, ],

    'security'           => [
        'blocked_extensions' => [ 'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'cgi', 'pl', 'py', 'sh', 'exe', 'bat', 'cmd', 'com', ],
        'validate_mime'      => true,
        'random_filename'    => true,
    ],

];

$upload_config = [

    'directories'        => [
        'cv'           => __DIR__ . '/../../storage/uploads/cv',
        'certificates' => __DIR__ . '/../../storage/uploads/certificates',
        'profile'      => __DIR__ . '/../../storage/uploads/profile',
    ],

    'allowed_extensions' => [
        'cv'           => [ 'pdf', 'doc', 'docx', ],
        'certificates' => [ 'pdf', ],
        'profile'      => [ 'jpg', 'png', 'webp', ],
    ],

    'allowed_mimes'      => [
        'cv'           => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        'certificates' => [ 'application/pdf',],
        'profile'      => [ 'image/jpeg', 'image/png', 'image/webp', ],
    ],

    'max_size'           => [
        'cv'           => 5 * 1024 * 1024,
        'certificates' => 5 * 1024 * 1024,
        'profile'      => 2 * 1024 * 1024,
    ],

];