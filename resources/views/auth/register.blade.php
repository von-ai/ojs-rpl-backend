@extends('layouts.auth')

@section('title', 'Daftar Akun SIRIDA | BRIDA Makassar')

@section('content')
<div class="w-full max-w-md lg:max-w-5xl mx-auto my-auto py-6">
    <!-- Main Register Card Container -->
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">

        <!-- Sisi Kiri: Panel Informasi (Desktop Lg Ke Atas) -->
        <div class="hidden lg:flex lg:col-span-4 xl:col-span-5 bg-surface-subtle p-7 sm:p-9 lg:p-10 border-r border-gray-200/90 flex-col justify-between">
            <div>
                <!-- Subtitle Category -->
                <span class="text-[11px] font-bold tracking-wider text-brand-maroon uppercase block mb-3">
                    PORTAL SIRIDA
                </span>

                <!-- Main Headline -->
                <h2 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight tracking-tight mb-3">
                    Mari bergabung bersama kami.
                </h2>

                <!-- Description -->
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    Buat akun untuk mulai mempublikasikan riset Anda dan mendukung inovasi daerah Kota Makassar.
                </p>

                <div class="border-b border-gray-200/80 mb-6"></div>

                <!-- Requirement List -->
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-brand-maroon shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600 leading-snug">Gunakan <span class="font-bold text-gray-900">email aktif</span> untuk keperluan verifikasi akun dan notifikasi.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-brand-maroon shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600 leading-snug">Username hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-brand-maroon shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-600 leading-snug">Kata sandi minimal <span class="font-bold text-gray-900">12 karakter</span> untuk keamanan akun Anda.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Left Branding -->
            <div class="pt-8 mt-6">
                <p class="text-xs font-bold text-brand-maroon">BRIDA Kota Makassar</p>
                <p class="text-[11px] text-gray-500">Badan Riset dan Inovasi Daerah</p>
            </div>
        </div>

        <!-- Sisi Kanan: Form Registrasi -->
        <div class="lg:col-span-8 xl:col-span-7 bg-white p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
            <div>
                <!-- Form Header -->
                <div class="mb-6 sm:mb-7">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                        Daftar akun SIRIDA
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                        Isi data berikut dengan benar, lalu verifikasi email Anda untuk mengaktifkan akun.
                    </p>
                </div>

                <!-- Global Error Banner Component -->
                @if ($errors->any())
                    <x-alert type="error" title="Pendaftaran Gagal" class="mb-5">
                        Mohon periksa kembali isian formulir Anda di bawah ini.
                    </x-alert>
                @endif

                <!-- Form Register -->
                <form method="POST" action="{{ route('register.store') }}" id="registerForm" class="space-y-4" novalidate>
                    @csrf

                    <!-- Row 1: Nama Lengkap -->
                    <div>
                        <x-label for="full_name" value="Nama Lengkap" />
                        <x-input
                            type="text"
                            name="full_name"
                            id="full_name"
                            :value="old('full_name')"
                            :error="$errors->has('full_name')"
                            minlength="2"
                            maxlength="200"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Contoh: Budi Santoso"
                        />
                        @error('full_name')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Row 2: Username & Email (Grid on sm) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-label for="username" value="Username" />
                            <x-input
                                type="text"
                                name="username"
                                id="username"
                                :value="old('username')"
                                :error="$errors->has('username')"
                                pattern="[A-Za-z0-9_.-]{3,50}"
                                minlength="3"
                                maxlength="50"
                                required
                                autocomplete="username"
                                placeholder="budi_santoso"
                            />
                            @error('username')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-label for="email" value="Alamat Email" />
                            <x-input
                                type="email"
                                name="email"
                                id="email"
                                :value="old('email')"
                                :error="$errors->has('email')"
                                maxlength="255"
                                required
                                autocomplete="email"
                                placeholder="budi@contoh.com"
                            />
                            @error('email')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Row 3: Afiliasi Institusi -->
                    <div>
                        <x-label for="affiliation" value="Afiliasi Institusi" />
                        <x-input
                            type="text"
                            name="affiliation"
                            id="affiliation"
                            :value="old('affiliation')"
                            :error="$errors->has('affiliation')"
                            minlength="2"
                            maxlength="255"
                            required
                            autocomplete="organization"
                            placeholder="Contoh: Universitas Hasanuddin"
                        />
                        @error('affiliation')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Row 4: Password & Konfirmasi (Grid on sm) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-label for="password" value="Kata Sandi" />
                            <div class="relative">
                                <x-input
                                    type="password"
                                    name="password"
                                    id="password"
                                    :error="$errors->has('password')"
                                    minlength="12"
                                    maxlength="128"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Minimal 12 karakter"
                                    class="pl-3.5 pr-10"
                                />
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1" data-target="password">
                                    <svg class="eye-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908A8.982 8.982 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.165-3.414a3 3 0 104.243-4.243m-4.243 4.243L3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-label for="password_confirmation" value="Konfirmasi Kata Sandi" />
                            <div class="relative">
                                <x-input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    :error="$errors->has('password_confirmation')"
                                    minlength="12"
                                    maxlength="128"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Ulangi kata sandi"
                                    class="pl-3.5 pr-10"
                                />
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1" data-target="password_confirmation">
                                    <svg class="eye-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908A8.982 8.982 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.165-3.414a3 3 0 104.243-4.243m-4.243 4.243L3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button Component -->
                    <div class="pt-4">
                        <x-button id="submitBtn">
                            <span id="btnText">Daftar Akun</span>
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

            <!-- Bottom Login Link Inside Card -->
            <div class="mt-6 pt-5 text-center text-xs text-gray-500 border-t border-gray-100">
                <span>Sudah memiliki akun?</span>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="ml-1 text-brand-maroon font-bold hover:underline">
                        Masuk di sini
                    </a>
                @else
                    <a href="{{ route('journal.about.contact') }}" class="ml-1 text-brand-maroon font-bold hover:underline">
                        Masuk di sini
                    </a>
                @endif
                <span class="mx-2 text-gray-300">|</span>
                <a href="{{ route('verification.notice') }}" class="text-gray-500 hover:text-brand-maroon transition-colors hover:underline">
                    Kirim ulang verifikasi
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle multiple password toggles
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const eyeIcon = this.querySelector('.eye-icon');
                const eyeOffIcon = this.querySelector('.eye-off-icon');

                if (passwordInput) {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');

                    eyeIcon.classList.toggle('hidden', isPassword);
                    eyeOffIcon.classList.toggle('hidden', !isPassword);
                }
            });
        });

        // Form Submit Loading State
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');

        if (registerForm && submitBtn) {
            registerForm.addEventListener('submit', function () {
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
