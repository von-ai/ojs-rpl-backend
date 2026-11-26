@extends('layouts.auth')

@section('title', 'Masuk ke Portal Jurnal | BRIDA Makassar')

@section('content')
<div class="w-full max-w-md mx-auto my-auto">
    <div class="bg-white rounded-2xl border border-outline-variant shadow-sm">
        <div class="p-5 sm:p-8 flex flex-col justify-between">
            <div>
                <!-- Form Header -->
                <div class="mb-6 sm:mb-7">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                        Masuk ke portal jurnal
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                        Gunakan email atau nama pengguna.
                    </p>
                </div>

                <!-- Session Status Notification Component -->
                @if (session('status'))
                    <x-alert type="success" class="mb-5">
                        {{ session('status') }}
                    </x-alert>
                @endif

                <!-- Global Error Banner Component -->
                @if ($errors->any())
                    <x-alert type="error" title="Gagal Masuk" class="mb-5">
                        Mohon periksa kembali email dan kata sandi Anda.
                    </x-alert>
                @endif

                <!-- Form Login -->
                <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-4 sm:space-y-5" novalidate>
                    @csrf

                    <!-- Input 1: Email atau nama pengguna -->
                    <div>
                        <x-label for="login" value="Email atau nama pengguna" />
                        <x-input
                            type="text"
                            name="login"
                            id="login"
                            :value="old('login', old('email'))"
                            :error="$errors->has('login') || $errors->has('email')"
                            required
                            autofocus
                            autocomplete="username"
                            aria-invalid="{{ ($errors->has('login') || $errors->has('email')) ? 'true' : 'false' }}"
                            aria-describedby="{{ ($errors->has('login') || $errors->has('email')) ? 'login-error' : '' }}"
                        />
                        @if ($errors->has('login'))
                            <p id="login-error" class="mt-1.5 text-xs text-red-600 font-medium">{{ $errors->first('login') }}</p>
                        @elseif ($errors->has('email'))
                            <p id="login-error" class="mt-1.5 text-xs text-red-600 font-medium">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <!-- Input 2: Password -->
                    <div>
                        <x-label for="password" value="Kata sandi" />
                        <div class="relative">
                            <x-input
                                type="password"
                                name="password"
                                id="password"
                                :error="$errors->has('password')"
                                required
                                autocomplete="current-password"
                                class="pl-3.5 pr-10 sm:pl-4 sm:pr-11"
                                aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                            />
                            <!-- Eye Toggle Button -->
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none transition-colors p-1"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                            >
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg id="eyeOffIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908A8.982 8.982 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.165-3.414a3 3 0 104.243-4.243m-4.243 4.243L3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p id="password-error" class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Checkbox & Forgot Password Row -->
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="w-4 h-4 rounded border-outline text-brand-maroon focus:ring-brand-maroon cursor-pointer transition"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="ml-2 text-xs font-medium text-gray-600">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-brand-maroon hover:text-brand-maroon-hover transition-colors">
                                Lupa kata sandi?
                            </a>
                        @else
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-brand-maroon hover:text-brand-maroon-hover transition-colors">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button Component -->
                    <div class="pt-2">
                        <x-button id="submitBtn">
                            <span id="btnText">Masuk</span>
                            <svg id="btnIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                            <svg id="btnSpinner" class="w-4 h-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </x-button>
                    </div>
                </form>
            </div>

            <!-- Bottom Register Link Inside Card -->
            <div class="mt-6 sm:mt-8 pt-5 sm:pt-6 text-center text-xs text-gray-500">
                <span>Belum punya akun?</span>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="ml-1 text-brand-maroon font-bold hover:underline">
                        Daftar akun
                    </a>
                @else
                    <a href="{{ route('journal.about.contact') }}" class="ml-1 text-brand-maroon font-bold hover:underline">
                        Daftar akun
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Below Card Help Contact Link -->
    <div class="mt-5 text-center text-xs text-gray-500 flex items-center justify-center gap-1.5 flex-wrap px-2">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Butuh bantuan akses?</span>
        <a href="mailto:jurnal.brida@makassarkota.go.id" class="text-brand-maroon font-bold hover:underline">
            jurnal.brida@makassarkota.go.id
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                eyeIcon.classList.toggle('hidden', isPassword);
                eyeOffIcon.classList.toggle('hidden', !isPassword);
            });
        }

        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');

        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                btnText.textContent = 'Memproses...';
                if (btnIcon) btnIcon.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');
            });
        }
    });
</script>
@endpush
