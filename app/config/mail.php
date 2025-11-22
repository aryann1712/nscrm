<?php
// Mail config now loaded from this file (preferred). Keep secrets out of version control.
return [
    'host' => 'nscrm.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'noreply@nscrm.com',
    'password' => 'NSTech@#11',
    'from_email' => 'noreply@nscrm.com',
    'from_name'  => 'NS Technology',
    // Troubleshooting flags (set to false in production)
    'debug' => 2, // 0=off, 2=client/server messages
    'allow_self_signed' => true,
];
