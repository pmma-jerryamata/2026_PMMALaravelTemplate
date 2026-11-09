<?php

return [
    // Single-host admission limit. Keep below MariaDB max_connections, leaving room for other apps/workers.
    'enabled' => env('HELPDESK_CAPACITY_ENABLED', true),
    'requests' => (int) env('HELPDESK_MAX_CONCURRENT_REQUESTS', 20),
    'directory' => storage_path('framework/capacity'),
];
