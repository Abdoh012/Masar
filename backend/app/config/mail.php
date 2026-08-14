<?php

return [
    'driver'         => getenv('MAIL_DRIVER') ?: 'smtp',
    'host'           => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    'port'           => getenv('MAIL_PORT') ?: 587,
    'encryption'     => getenv('MAIL_ENCRYPTION') ?: 'tls',
    'username'       => getenv('MAIL_USERNAME') ?: '',
    'password'       => getenv('MAIL_PASSWORD') ?: '',
    'from'           => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: (getenv('MAIL_USERNAME') ?: 'no-reply@masar.local'),
        'name'    => getenv('MAIL_FROM_NAME') ?: 'MASAR',
    ],
    'reply_to'       => [
        'address' => getenv('MAIL_REPLY_TO_ADDRESS') ?: (getenv('MAIL_FROM_ADDRESS') ?: (getenv('MAIL_USERNAME') ?: 'no-reply@masar.local')),
        'name'    => getenv('MAIL_REPLY_TO_NAME') ?: (getenv('MAIL_FROM_NAME') ?: 'MASAR'),
    ],
    'verification'   => ['enabled' => filter_var(getenv('MAIL_VERIFICATION_ENABLED') ?: true, FILTER_VALIDATE_BOOLEAN)],
    'password_reset' => ['enabled' => true, 'token_expiration_minutes' => 60],
    'notifications'  => ['application_accepted' => true, 'application_rejected' => true, 'certificate_approved' => true, 'certificate_rejected' => true, 'new_message' => false, 'training_updated' => false, 'training_closed' => false, 'trial_expiring' => true],
];
