<?php

use App\Http\Middleware\EnforcePlatformPolicy;
use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\EnsurePortalRoleAccess;
use App\Http\Middleware\LoadPlatformSettings;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustedHosts;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustHosts;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts(at: function () {
            $hosts = [(string) parse_url(config('app.url'), PHP_URL_HOST)];
            $hosts = array_merge($hosts, array_filter(array_map('trim', explode(',', (string) env('TRUSTED_HOSTS', '')))));
            if (! app()->environment('production')) {
                $hosts = array_merge($hosts, ['localhost', '127.0.0.1', '[::1]']);
            }

            $patterns = array_map(fn ($host) => '^(.+\.)?'.preg_quote($host).'$', array_filter($hosts));
            $patterns[] = '^.+\.azurecontainerapps\.io$';

            return array_values(array_unique($patterns));
        }, subdomains: true);
        $middleware->replace(TrustHosts::class, TrustedHosts::class);
        $proxies = env('TRUSTED_PROXIES', '*');
        if ($proxies) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : array_filter(array_map('trim', explode(',', $proxies))));
        }
        $middleware->append(SecurityHeaders::class);
        $middleware->web(prepend: [LoadPlatformSettings::class], append: [EnsureActiveAccount::class, EnforcePlatformPolicy::class]);

        $middleware->alias([
            'role.access' => EnsurePortalRoleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'token']);
    })->create();
