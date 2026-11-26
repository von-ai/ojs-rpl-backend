@extends('layouts.auth')

@section('title', 'Daftar Akun SIRIDA | BRIDA Makassar')

@section('content')
<div class="w-full max-w-md lg:max-w-5xl mx-auto my-auto">
    <!-- Main Register Card Container -->
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">

        <!-- Sisi Kiri: Panel Informasi (Desktop Lg Ke Atas) -->
        <div class="hidden lg:flex lg:col-span-5 bg-surface-subtle p-7 sm:p-9 lg:p-10 border-r border-gray-200/90 flex-col justify-between">
            <div>
                <!-- Subtitle Category -->
                <span class="text-[11px] font-bold tracking-wider text-brand-maroon uppercase block mb-3">
                    PORTAL JURNAL SIRIDA
                </span>

                <!-- Main Headline -->
                <h2 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight tracking-tight mb-3">
                    Bergabung dan publikasikan riset Anda.
                </h2>

                <!-- Description -->
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    Daftarkan akun untuk mengajukan naskah riset, memantau proses telaah, dan berkontribusi dalam ekosistem inovasi Kota Makassar.
                </p>

                <div class="border-b border-gray-200/80 mb-6"></div>

                <!-- Feature Guidance List (Konsisten dengan Login Card) -->
                <div class="space-y-5">
                    <!-- Feature 1: Email Aktif -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-red-50/70 border border-red-100 flex items-center justify-center shrink-0 text-brand-maroon mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Gunakan email aktif</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Tautan aktivasi akun dan seluruh notifikasi alur editorial dikirim ke email ini.</p>
                        </div>
                    </div>

                    <!-- Feature 2: Identitas & Afiliasi Riil -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-red-50/70 border border-red-100 flex items-center justify-center shrink-0 text-brand-maroon mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Identitas &amp; afiliasi riil</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Cantumkan nama lengkap dan institusi akademik yang benar untuk metadata kepenulisan.</p>
                        </div>
                    </div>

                    <!-- Feature 3: Keamanan Sandi -->
                    <div class="flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-red-50/70 border border-red-100 flex items-center justify-center shrink-0 text-brand-maroon mt-0.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 leading-snug">Keamanan sandi terjamin</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Minimal 12 karakter untuk melindungi hak kepenulisan dan integritas data riset.</p>
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

        <!-- Sisi Kanan: Form Registrasi -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-8 lg:p-10 flex flex-col justify-between">
            <div>
                <!-- Form Header -->
                <div class="mb-5 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-1">
                        Daftar akun SIRIDA
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                        Lengkapi isian data berikut untuk membuat akun dan memulai aktivitas di portal.
                    </p>
                </div>

                <!-- Global Error Banner Component -->
                @if ($errors->any())
                    <x-alert type="error" title="Pendaftaran Gagal" class="mb-4">
                        Mohon periksa kembali isian formulir Anda di bawah ini.
                    </x-alert>
                @endif

                <!-- Form Register -->
                <form method="POST" action="{{ route('register.store') }}" id="registerForm" class="space-y-3.5 sm:space-y-4" novalidate>
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
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Row 2: Username & Email (Grid on sm) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
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
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
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
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
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
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Row 4: Password & Konfirmasi (Grid on sm) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
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
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1 transition-colors" data-target="password" aria-label="Tampilkan atau sembunyikan kata sandi">
                                    <svg class="eye-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908A8.982 8.982 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.165-3.414a3 3 0 104.243-4.243m-4.243 4.243L3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
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
                                <button type="button" class="toggle-password absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-1 transition-colors" data-target="password_confirmation" aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi">
                                    <svg class="eye-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="eye-off-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908A8.982 8.982 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m-6.165-3.414a3 3 0 104.243-4.243m-4.243 4.243L3 3l18 18"/></svg>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Row 5: Ketentuan Layanan & Kebijakan Privasi Accordion Card -->
                    <div class="rounded-xl border border-gray-200/90 bg-gray-50/70 p-3 sm:p-3.5 text-xs transition-colors hover:bg-gray-50">
                        <details class="group [&_summary::-webkit-details-marker]:hidden">
                            <summary class="flex items-center justify-between cursor-pointer list-none text-xs font-semibold text-gray-800 hover:text-brand-maroon transition-colors select-none">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-brand-maroon shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    <span>Ketentuan Layanan &amp; Kebijakan Privasi</span>
                                </span>
                                <span class="flex items-center gap-1.5 text-[11px] font-medium text-gray-400 group-hover:text-gray-600 transition-colors">
                                    <span class="hidden sm:inline font-normal">Lihat ringkasan</span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200 group-open:rotate-180 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </span>
                            </summary>
                            <div class="mt-2.5 pt-2.5 border-t border-gray-200/80 space-y-2 text-[11px] text-gray-600 leading-relaxed">
                                <div class="flex items-start gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-700 uppercase tracking-wide shrink-0 mt-0.5">Tujuan</span>
                                    <p>{{ config('registration.purpose') }}</p>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-700 uppercase tracking-wide shrink-0 mt-0.5">Ketentuan</span>
                                    <p>{{ config('registration.terms') }}</p>
                                </div>
                                <div class="flex items-start gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-700 uppercase tracking-wide shrink-0 mt-0.5">Privasi</span>
                                    <p>{{ config('registration.privacy') }}</p>
                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Hidden Notice Version Input for Consent Auditing -->
                    <input type="hidden" name="notice_version" value="{{ config('registration.version') }}">
                    @error('notice_version')
                        <p class="text-xs text-red-600 font-medium">Ketentuan telah diperbarui. Baca kembali sebelum mendaftar.</p>
                    @enderror

                    <!-- Row 6: Consent Checkbox -->
                    <div class="pt-0.5">
                        <div class="flex items-start gap-2.5">
                            <input
                                type="checkbox"
                                name="consent_terms"
                                id="consent_terms"
                                value="1"
                                required
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon focus:ring-offset-0 cursor-pointer transition"
                                {{ old('notice_version') === config('registration.version') && old('consent_terms') ? 'checked' : '' }}
                            />
                            <label for="consent_terms" class="text-xs text-gray-600 leading-snug cursor-pointer select-none">
                                Saya menyetujui <span class="font-semibold text-gray-900">Ketentuan Layanan</span> dan <span class="font-semibold text-gray-900">Kebijakan Privasi</span> SIRIDA serta memberikan persetujuan pemrosesan data pribadi untuk pembuatan dan verifikasi akun. <span class="text-brand-maroon font-bold">*</span>
                            </label>
                        </div>
                        <p id="consentError" class="mt-1.5 text-xs text-red-600 font-medium hidden">
                            Anda wajib menyetujui Ketentuan Layanan dan Kebijakan Privasi untuk mendaftar.
                        </p>
                        @error('consent_terms')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Row 7: Submit Button -->
                    <div class="pt-2">
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
            <div class="mt-5 pt-4 text-center text-xs text-gray-500 border-t border-gray-100">
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

    <!-- Below Card Help Contact Link (Konsisten dengan Login Card) -->
    <div class="mt-5 text-center text-xs text-gray-500 flex items-center justify-center gap-1.5 flex-wrap px-2">
        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Butuh bantuan pendaftaran?</span>
        <a href="mailto:jurnal.brida@makassarkota.go.id" class="text-brand-maroon font-bold hover:underline">
            jurnal.brida@makassarkota.go.id
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Handle password visibility toggles
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

        // Form Submit Loading State & Consent Guard
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');
        const consentCheckbox = document.getElementById('consent_terms');
        const consentError = document.getElementById('consentError');

        if (consentCheckbox && consentError) {
            consentCheckbox.addEventListener('change', function () {
                if (this.checked) {
                    consentError.classList.add('hidden');
                }
            });
        }

        if (registerForm && submitBtn) {
            registerForm.addEventListener('submit', function (e) {
                if (consentCheckbox && !consentCheckbox.checked) {
                    e.preventDefault();
                    if (consentError) {
                        consentError.classList.remove('hidden');
                    }
                    consentCheckbox.focus();
                    return false;
                }

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