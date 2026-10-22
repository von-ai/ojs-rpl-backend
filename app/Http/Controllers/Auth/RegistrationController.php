<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\IdentityException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\IdentityRequest;
use App\Http\Resources\RegistrationUserResource;
use App\Services\Identity\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function form()
    {
        return view('auth.register');
    }

    public function notice()
    {
        return view('auth.verification-notice');
    }

    public function verificationForm(Request $request)
    {
        return response()->view('auth.verify', ['token' => $request->query('token')])
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function register(IdentityRequest $request, RegistrationService $service)
    {
        try {
            $user = $service->register($request->validated());
        } catch (IdentityException $exception) {
            if ($request->is('api/*')) {
                throw $exception;
            }

            return back()->withErrors(['email' => $exception->getMessage()])
                ->withInput($request->except(['password', 'password_confirmation', '_token']));
        }
        if ($request->is('api/*')) {
            return response()->json([
                'data' => (new RegistrationUserResource($user))->resolve($request),
                'meta' => ['request_id' => $request->attributes->get('request_id')],
            ], 201)->header('Location', '/register/check-email');
        }

        return redirect()->route('verification.notice')->with('registered', true);
    }

    public function verify(IdentityRequest $request, RegistrationService $service)
    {
        try {
            $user = $service->verify($request->validated('token'));
        } catch (IdentityException $exception) {
            if ($request->is('api/*')) {
                throw $exception;
            }

            return response()->view('auth.verification-notice', ['verificationError' => $exception->getMessage()], $exception->status);
        }
        if ($request->is('api/*')) {
            return response()->json([
                'data' => (new RegistrationUserResource($user))->resolve($request),
                'meta' => ['request_id' => $request->attributes->get('request_id')],
            ]);
        }

        return redirect()->route('verification.notice')->with('verified', true);
    }

    public function resend(IdentityRequest $request, RegistrationService $service)
    {
        $service->resend($request->validated('email'));
        if ($request->is('api/*')) {
            return response('', 202);
        }

        return redirect()->route('verification.notice')->with('resent', true);
    }
}
