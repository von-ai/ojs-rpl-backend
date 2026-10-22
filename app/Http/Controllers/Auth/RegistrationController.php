<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\IdentityException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\IdentityRequest;
use App\Services\Identity\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function form(): View
    {
        return view('auth.register');
    }

    public function notice(): View
    {
        return view('auth.verification-notice');
    }

    public function verificationForm(Request $request): Response
    {
        return response()->view('auth.verify', ['token' => $request->query('token')])
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function register(IdentityRequest $request, RegistrationService $service): RedirectResponse
    {
        try {
            $service->register($request->validated());
        } catch (IdentityException $exception) {
            return back()->withErrors(['email' => $exception->getMessage()])
                ->withInput($request->except(['password', 'password_confirmation', '_token']));
        }

        return redirect()->route('verification.notice')->with('registered', true);
    }

    public function verify(IdentityRequest $request, RegistrationService $service): RedirectResponse|Response
    {
        try {
            $service->verify($request->validated('token'));
        } catch (IdentityException $exception) {
            return response()->view('auth.verification-notice', ['verificationError' => $exception->getMessage()], $exception->status);
        }

        return redirect()->route('verification.notice')->with('verified', true);
    }

    public function resend(IdentityRequest $request, RegistrationService $service): RedirectResponse
    {
        $service->resend($request->validated('email'));

        return redirect()->route('verification.notice')->with('resent', true);
    }
}
