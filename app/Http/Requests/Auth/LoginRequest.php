<?php

namespace App\Http\Requests\Auth;

use App\Services\Auth\AuthenticationService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Supports both `login` (email/username) and `email` field inputs.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required_without:email', 'nullable', 'string', 'max:254'],
            'email' => ['required_without:login', 'nullable', 'string', 'max:254'],
            'password' => ['required', 'string', 'max:128'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required_without' => 'Alamat email atau username wajib diisi.',
            'email.required_without' => 'Alamat email atau username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials with rate limiting and database persistence.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $loginInput = (string) ($this->input('login') ?? $this->input('email'));
        $password = (string) $this->input('password');
        $remember = $this->boolean('remember');

        $authService = app(AuthenticationService::class);

        try {
            $user = $authService->authenticateWeb($loginInput, $password, $this->ip());
            if ($remember) {
                $user->setRememberToken(Str::random(60));
                $user->remember_expires_at = now()->addDay();
                $user->save();
            }
            Auth::guard()->setRememberDuration(1440);
            Auth::login($user, $remember);
            RateLimiter::clear($this->throttleKey());
        } catch (ValidationException $e) {
            RateLimiter::hit($this->throttleKey(), 900);
            throw $e;
        }
    }

    /**
     * Ensure the login request is not rate limited.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $message = trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]);

        $headers = ['Retry-After' => (string) $seconds];
        $errors = ['login' => [$message], 'email' => [$message]];
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json(['message' => $message, 'errors' => $errors], 429, $headers));
        }

        $this->flashOnly(['login', 'email']);
        $viewErrors = (new ViewErrorBag)->put('default', new MessageBag($errors));
        throw new HttpResponseException(response()->view('auth.login', ['errors' => $viewErrors], 429, $headers));
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $loginInput = (string) ($this->input('login') ?? $this->input('email'));

        return hash_hmac('sha256', Str::lower(trim($loginInput)).'|'.$this->ip(), (string) config('app.key'));
    }
}
