<?php
// config/mail.php

return [
    'driver' => 'smtp',
    'host' => getenv('MAIL_HOST') ?: getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'port' => getenv('MAIL_PORT') ?: getenv('SMTP_PORT') ?: 587,
    'username' => getenv('MAIL_USERNAME') ?: getenv('SMTP_USER') ?: 'user@example.com',
    'password' => getenv('MAIL_PASSWORD') ?: getenv('SMTP_PASS') ?: 'secret',
    'encryption' => getenv('MAIL_ENCRYPTION') ?: getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@staffsync.com',
    'from_name' => getenv('MAIL_FROM_NAME') ?: 'StaffSync HRMS',
];
