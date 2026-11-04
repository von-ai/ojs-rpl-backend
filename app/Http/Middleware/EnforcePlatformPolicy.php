<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnforcePlatformPolicy
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->isMethodSafe() && ! $request->routeIs('logout', 'role.switch')) {
            $key = 'portal-mutation:'.hash_hmac('sha256', $request->user()->id, (string) config('app.key'));
            try {
                if (RateLimiter::tooManyAttempts($key, 60)) {
                    abort(429, 'Terlalu banyak permintaan. Silakan coba lagi sebentar.');
                }
                RateLimiter::hit($key, 60);
            } catch (HttpException $exception) {
                throw $exception;
            } catch (\Throwable $exception) {
                abort(503, 'Pembatas permintaan sementara tidak tersedia.');
            }
        }
        $settings = $request->attributes->get('platform.settings', []);
        if (($settings['maintenance_mode'] ?? false) && ! $request->user()?->isSysAdmin() && ! $request->routeIs('login', 'logout', 'password.*')) {
            return response()->view('errors.maintenance', ['message' => $settings['maintenance_message']], 503)->header('Retry-After', '300');
        }
        $extensions = array_filter(array_map(fn ($extension) => strtolower(trim($extension)), explode(',', $settings['allowed_file_extensions'] ?? 'pdf,doc,docx,odt,jpg,jpeg,png,csv,html')));
        $check = function (array $files) use (&$check, $settings, $extensions): void {
            foreach ($files as $name => $file) {
                if (is_array($file)) {
                    $check($file);

                    continue;
                }
                if (! $file->isValid() || $file->getSize() > ($settings['max_upload_size_mb'] ?? 20) * 1048576 || ! in_array(strtolower($file->getClientOriginalExtension()), $extensions, true)) {
                    throw ValidationException::withMessages([$name => 'Ukuran atau ekstensi berkas tidak sesuai kebijakan unggahan platform.']);
                }
            }
        };
        $check($request->allFiles());

        return $next($request);
    }
}
