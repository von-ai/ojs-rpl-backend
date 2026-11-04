<?php

namespace App\Providers;

use App\Services\Storage\AzureBlobStorageAdapter;
use App\Services\Storage\AzureBlobStorageClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('identity', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perHour(100)->by($request->ip()),
            ];
        });

        // Registrasi Driver Azure Blob Storage & Azurite Emulator (NF-01, NF-02, NF-08)
        Storage::extend('azure', function ($app, array $config) {
            $client = new AzureBlobStorageClient(
                accountName: (string) ($config['name'] ?? ''),
                accountKey: (string) ($config['key'] ?? ''),
                container: (string) ($config['container'] ?? 'sirida-manuscripts'),
                baseUrl: ! empty($config['url']) ? (string) $config['url'] : null,
                prefix: ! empty($config['prefix']) ? (string) $config['prefix'] : null,
            );

            $adapter = new AzureBlobStorageAdapter($client);

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
