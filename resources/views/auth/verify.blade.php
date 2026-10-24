@extends('layouts.auth')

@section('title', 'Konfirmasi Email SIRIDA | BRIDA Makassar')

@section('content')
<div class="w-full max-w-md lg:max-w-5xl mx-auto my-auto py-6">
    <!-- Main Verify Card Container -->
    <div class="bg-white rounded-2xl border border-gray-200/90 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">
        
        <!-- Sisi Kiri: Panel Informasi (Desktop Lg Ke Atas) -->
        <div class="hidden lg:flex lg:col-span-5 bg-surface-subtle p-7 sm:p-9 lg:p-10 border-r border-gray-200/90 flex-col justify-between">
            <div>
                <!-- Subtitle Category -->
                <span class="text-[11px] font-bold tracking-wider text-brand-maroon uppercase block mb-3">
                    VERIFIKASI AKUN
                </span>
                
                <!-- Main Headline -->
                <h2 class="text-2xl sm:text-[26px] font-bold text-gray-900 leading-tight tracking-tight mb-3">
                    Langkah terakhir menuju akses penuh.
                </h2>
                
                <!-- Description -->
                <p class="text-sm text-gray-600 leading-relaxed mb-6">
                    Keamanan akun Anda adalah prioritas kami. Verifikasi email diperlukan untuk memastikan bahwa ini benar-benar Anda.
                </p>

                <div class="border-b border-gray-200/80 mb-6"></div>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Akses Dasbor Publikasi</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Mulai ajukan dan pantau naskah riset Anda.</p>
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

        <!-- Sisi Kanan: Area Aksi Verifikasi -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
            <div class="max-w-md mx-auto w-full">
                <!-- Icon Header -->
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center text-brand-maroon mb-6 border border-red-100 shadow-sm">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>

                <!-- Form Header -->
                <div class="mb-8">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mb-2">
                        Konfirmasi alamat email
                    </h1>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Terima kasih telah mendaftar di Portal SIRIDA. Silakan konfirmasi kepemilikan email Anda untuk melanjutkan.
                    </p>
                </div>

                <!-- Pengecekan Token -->
                @if (is_string($token) && strlen($token) >= 20 && strlen($token) <= 512)
                    <!-- Form Verifikasi -->
                    <form method="POST" action="{{ route('verification.verify') }}" id="verifyForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <x-button id="submitBtn" class="w-full justify-center text-base py-3">
                            <span id="btnText">Verifikasi Email Saya</span>
                            <svg id="btnIcon" class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg id="btnSpinner" class="w-5 h-5 ml-2 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </x-button>
                    </form>
                @else
                    <!-- State Token Tidak Valid -->
                    <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-bold text-red-800">Tautan Tidak Valid</h3>
                                <p class="text-sm text-red-700 mt-1">Tautan verifikasi yang Anda gunakan tidak valid, rusak, atau mungkin telah kedaluwarsa.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tautan Bantuan / Resend -->
                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-sm text-gray-500">
                        Tidak menerima email atau tautan kedaluwarsa?
                    </p>
                    <a href="{{ route('verification.notice') }}" class="inline-flex items-center gap-1.5 mt-2 text-sm font-bold text-brand-maroon hover:text-brand-maroon-hover hover:underline transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Kirim ulang tautan verifikasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const verifyForm = document.getElementById('verifyForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');

        if (verifyForm && submitBtn) {
            verifyForm.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
                btnText.textContent = 'Memverifikasi...';
                if (btnIcon) btnIcon.classList.add('hidden');
                if (btnSpinner) btnSpinner.classList.remove('hidden');
            });
        }
    });
</script>
@endpush