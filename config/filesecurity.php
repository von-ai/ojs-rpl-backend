<?php

return [
    'disk' => env('PRIVATE_UPLOAD_DISK', 'azure'),
    'driver' => env('FILE_SCAN_DRIVER', 'mock'),
    'event_grid_secret' => env('DEFENDER_EVENT_GRID_SECRET', null),
];
