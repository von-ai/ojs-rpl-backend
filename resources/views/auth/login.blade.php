@extends('layouts.auth')

@section('title', 'Masuk ke Portal Jurnal | BRIDA Makassar')

@section('content')
<div class="w-full max-w-md lg:max-w-5xl mx-auto my-auto">
    <!-- Main Login Card Container -->
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- Sisi Kiri: Panel Informasi Portal (Desktop Lg Ke Atas) -->
        <div class="hidden lg:flex lg:col-span-5 bg-surface-subtle p-7 sm:p-9 lg:p-10 border-r border-gray-200/90 flex-col justify-between">
            <div>
                <!-- Subtitle Category -->
                <span class="text-[11px] font-bold tracking-wider text-brand-maroon uppercase block mb-3">
                    PORTAL JURNAL ILMIAH
                </span>
                
                <!-- Main Headline -->
                <h2 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight tracking-tight mb-3">
                    Riset dan inovasi untuk Kota Makassar.
                </h2>
                
                <!-- Description -->
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    Ruang publikasi ilmiah untuk berbagi pengetahuan dan mendukung pembangunan daerah berbasis riset.
                </p>

                <div class="border-b border-gray-200/80 mb-6"></div>

                <!-- Feature List -->
                <div class="space-y-5">
                    <!-- Feature 1 -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-red-50/70 border border-red-100 flex items-center justify-center shrink-0 text-brand-maroon mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Ajukan naskah penelitian</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Kirim dan kelola naskah secara online.</p>
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-red-50/70 border border-red-100 flex items-center justify-center shrink-0 text-brand-maroon mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Terhubung dengan redaksi</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Tanggapi telaah dan revisi naskah.</p>
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-red-50/70 border border-red-100 flex items-center justify-center shrink-0 text-brand-maroon mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Pantau proses publikasi</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Ikuti perkembangan setiap pengajuan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Left Branding -->
            <div class="pt-8 mt-6">
                <p class="text-xs font-bold text-brand-maroon">BRIDA Kota Makassar</p>
                <p class="text-[11px] text-gray-500">Badan Riset dan Inovasi Daerah</p>
            </div>
        </div>

        <!-- Sisi Kanan: Form Login -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-10 lg:p-12 flex flex-col justify-between">
            <div>
                <!-- Form Header -->
                <div class="mb-6 sm:mb-7">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight mb-1.5 sm:mb-2">
                        Masuk ke portal jurnal
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                        Selamat datang kembali. Gunakan akun Anda untuk melanjutkan aktivitas di portal BRIDA Makassar.
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

                    <!-- Input 1: Email atau Username -->
                    <div>
                        <x-label for="login" value="Email atau Username" />
                        <x-input 
                            type="text" 
                            name="login" 
                            id="login" 
                            :value="old('login', old('email'))" 
                            :error="$errors->has('login') || $errors->has('email')"
                            required 
                            autofocus
                            placeholder="Masukkan email atau username" 
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
                                placeholder="Masukkan kata sandi" 
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
                                class="w-4 h-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon cursor-pointer transition"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="ml-2 text-xs font-medium text-gray-600">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-bold text-brand-maroon hover:text-brand-maroon-hover transition-colors">
                                Lupa kata sandi?
                            </a>
                        @else
                            <a href="#" class="text-xs font-bold text-brand-maroon hover:text-brand-maroon-hover transition-colors">
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
                <span>Belum memiliki akun?</span>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="ml-1 text-brand-maroon font-bold hover:underline">
                        Daftar akun
                    </a>
                @else
                    <a href="#" class="ml-1 text-brand-maroon font-bold hover:underline">
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
