@extends('layouts.auth')

@section('title', 'Verifikasi Email SIRIDA | BRIDA Makassar')

@section('content')
<div class="w-full max-w-md lg:max-w-5xl mx-auto my-auto py-6">
    <!-- Main Notice Card Container -->
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- Sisi Kiri: Panel Informasi (Desktop Lg Ke Atas) -->
        <div class="hidden lg:flex lg:col-span-5 bg-surface-subtle p-7 sm:p-9 lg:p-10 border-r border-gray-200/90 flex-col justify-between">
            <div>
                <!-- Subtitle Category -->
                <span class="text-[11px] font-bold tracking-wider text-brand-maroon uppercase block mb-3">
                    STATUS AKUN
                </span>
                
                <!-- Main Headline -->
                <h2 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight tracking-tight mb-3">
                    Keamanan akun Anda adalah prioritas.
                </h2>
                
                <!-- Description -->
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    Kami memerlukan verifikasi email untuk memastikan setiap aktivitas publikasi di Portal SIRIDA dilakukan oleh pengguna yang sah.
                </p>

                <div class="border-b border-gray-200/80 mb-6"></div>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-red-50 text-brand-maroon flex items-center justify-center shrink-0 mt-0.5 border border-red-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Periksa kotak masuk Anda</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Cek email (termasuk folder spam) untuk menemukan tautan verifikasi. Tautan berlaku selama 24 jam.</p>
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

        <!-- Sisi Kanan: Area Status / Form Resend -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
            
            @if (session('verified'))
                <!-- STATE: SUDAH TERVERIFIKASI -->
                <div class="text-center max-w-md mx-auto w-full">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center text-green-600 mx-auto mb-6 border border-green-100 shadow-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-3">
                        Email Terverifikasi!
                    </h1>
                    
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-left mb-8">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-blue-800 leading-relaxed" role="status">
                                Akun Anda kini berstatus aktif. Namun, mohon diperhatikan bahwa <strong>fitur login belum tersedia pada versi MVP ini</strong>.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 text-sm font-bold text-brand-maroon hover:text-brand-maroon-hover transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Halaman Awal
                    </a>
                </div>

            @else
                <!-- STATE: BELUM TERVERIFIKASI & FORM RESEND -->
                <div>
                    <!-- Form Header -->
                    <div class="mb-6 sm:mb-8">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 tracking-tight mb-2">
                            Verifikasi Email
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                            Buka tautan verifikasi yang telah kami kirimkan ke email Anda, lalu konfirmasikan. Tautan berlaku selama 24 jam.
                        </p>
                    </div>

                    <!-- Notifikasi Status -->
                    @if (isset($verificationError))
                        <x-alert type="error" class="mb-5" role="alert">
                            {{ $verificationError }}
                        </x-alert>
                    @endif

                    @if (session('registered'))
                        <x-alert type="success" title="Akun Berhasil Dibuat!" class="mb-5" role="status">
                            Silakan periksa kotak masuk atau folder spam email Anda untuk memverifikasi pendaftaran.
                        </x-alert>
                    @endif

                    @if (session('resent'))
                        <x-alert type="info" title="Tautan Dikirim" class="mb-5" role="status">
                            Jika akun memenuhi syarat, email verifikasi baru akan dikirim. Mohon tunggu setidaknya <strong>satu menit</strong> sebelum mencoba lagi.
                        </x-alert>
                    @endif

                    @if ($errors->any())
                        <x-alert type="error" title="Gagal Mengirim Ulang" class="mb-5" role="alert">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif

                    <div class="border-t border-gray-100 pt-6 mt-2">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Belum menerima email?</h3>
                        
                        <!-- Form Kirim Ulang -->
                        <form method="POST" action="{{ route('verification.resend') }}" id="resendForm" class="space-y-4">
                            @csrf
                            <div>
                                <x-label for="email" value="Alamat Email Terdaftar" />
                                <x-input 
                                    type="email" 
                                    name="email" 
                                    id="email" 
                                    maxlength="255"
                                    required 
                                    autocomplete="email"
                                    placeholder="Masukkan email Anda" 
                                />
                            </div>

                            <div class="pt-2">
                                <x-button id="submitBtn">
                                    <span id="btnText">Kirim Ulang Verifikasi</span>
                                    <svg id="btnIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <svg id="btnSpinner" class="w-4 h-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </x-button>
                            </div>
                        </form>
                    </div>

                    <!-- Bottom Register Link -->
                    <div class="mt-8 text-center text-sm text-gray-500">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 font-bold text-brand-maroon hover:text-brand-maroon-hover transition-colors hover:underline">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali ke Registrasi
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resendForm = document.getElementById('resendForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');

        if (resendForm && submitBtn) {
            resendForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                btnText.textContent = 'Mengirim Tautan...';
                if (btnIcon) btnIcon.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');
            });
        }
    });
</script>
@endpush