<?php

return [
    'disk' => env('PRIVATE_UPLOAD_DISK', 'local'),
    'driver' => env('FILE_SCAN_DRIVER', 'disabled'),
    'clamd_host' => env('CLAMD_HOST', '127.0.0.1'),
    'clamd_port' => (int) env('CLAMD_PORT', 3310),
    'clamav_binary' => env('CLAMAV_BINARY', '/usr/bin/clamscan'),
];
