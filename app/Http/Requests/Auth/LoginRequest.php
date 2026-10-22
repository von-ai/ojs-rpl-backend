<?php

namespace App\Http\Requests\Auth;

use App\Services\Auth\AuthenticationService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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
            'login' => ['required_without:email', 'nullable', 'string'],
            'email' => ['required_without:login', 'nullable', 'string'],
            'password' => ['required', 'string'],
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
            Auth::login($user, $remember);
            RateLimiter::clear($this->throttleKey());
        } catch (ValidationException $e) {
            RateLimiter::hit($this->throttleKey(), 900);
            throw $e;
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
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

        throw ValidationException::withMessages([
            'login' => $message,
            'email' => $message,
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $loginInput = (string) ($this->input('login') ?? $this->input('email'));

        return Str::transliterate(Str::lower($loginInput).'|'.$this->ip());
    }
}
