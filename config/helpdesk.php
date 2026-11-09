<?php

return [
    // Code/environment switch only. Relaxed passwords are limited to local/testing.
    // Set HELPDESK_STRICT_PASSWORDS=true to require the secure policy locally too.
    'strict_passwords' => env('HELPDESK_STRICT_PASSWORDS', false),
];
