<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class LoadPlatformSettings
{
    public function handle(Request $request, Closure $next)
    {
        $settings = SystemSetting::getAllSettings();
        $request->attributes->set('platform.settings', $settings);
        config(['app.name' => $settings['site_name'], 'session.lifetime' => min(120, max(15, $settings['session_lifetime_minutes']))]);
        view()->share('platformSettings', $settings);

        return $next($request);
    }
}
