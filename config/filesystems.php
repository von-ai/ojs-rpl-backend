<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Proyek ini sepenuhnya mengimplementasikan Azure Blob Storage (dan emulator
    | Azurite untuk pengujian/pengembangan lokal). Seluruh file fisik dikelola
    | secara terpusat pada object storage Azure.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'azure'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Konfigurasi disk object storage Azure untuk manuskrip aplikasi dan pencadangan.
    |
    */

    'disks' => [

        'azure' => [
            'driver' => 'azure',
            'name' => env('AZURE_STORAGE_NAME'),
            'key' => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER', 'sirida-manuscripts'),
            'url' => env('AZURE_STORAGE_URL'),
            'public_url' => env('AZURE_STORAGE_PUBLIC_URL'),
            'prefix' => env('AZURE_STORAGE_PREFIX', null),
            'throw' => false,
        ],

        'azure-backups' => [
            'driver' => 'azure',
            'name' => env('AZURE_STORAGE_NAME'),
            'key' => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_BACKUP_CONTAINER', 'sirida-backups'),
            'url' => env('AZURE_STORAGE_URL'),
            'public_url' => env('AZURE_STORAGE_PUBLIC_URL'),
            'prefix' => env('AZURE_BACKUP_PREFIX', null),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Seluruh file privat dan publik dilayani via stream controller dan SAS URL
    | Azure Blob Storage, sehingga symlink lokal tidak digunakan.
    |
    */

    'links' => [],

];
