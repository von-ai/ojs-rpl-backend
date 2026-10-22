<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class IdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['email', 'username'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => mb_strtolower(trim($this->input($field)))]);
            }
        }
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'register' => [
                'full_name' => ['required', 'string', 'min:2', 'max:200'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'username' => ['required', 'string', 'regex:/^[a-z0-9_.-]{3,50}$/'],
                'password' => ['required', 'string', 'min:12', 'max:128'],
                'affiliation' => ['required', 'string', 'min:2', 'max:255'],
            ],
            'verify' => ['token' => ['required', 'string', 'min:20', 'max:512']],
            'resend' => ['email' => ['required', 'string', 'email', 'max:255']],
        };
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $allowed = array_keys($this->rules());
            if (! $this->is('api/*')) {
                $allowed[] = '_token';
                if ($this->route()->getActionMethod() === 'register') {
                    $allowed[] = 'password_confirmation';
                    if ($this->input('password') !== $this->input('password_confirmation')) {
                        $validator->errors()->add('password', 'Konfirmasi password tidak sama.');
                    }
                }
            }
            foreach (array_diff(array_keys($this->all()), $allowed) as $field) {
                $validator->errors()->add($field, 'Field ini tidak diizinkan.');
            }
        });
    }
}
