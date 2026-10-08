@props(['helpResources' => null])

<div class="bg-white rounded-xl border border-gray-200 p-5 shadow-2xs space-y-4">
    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
        <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            <svg class="w-4 h-4 text-brand-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Bantuan & Layanan Penulis</span>
        </h2>
    </div>

    <!-- Info Box -->
    <div class="bg-gray-50 rounded-lg p-3.5 border border-gray-200/80 space-y-2 text-xs">
        <span class="text-[10px] font-bold text-brand-maroon uppercase tracking-wider block">
            PUSAT INFORMASI BRIDA
        </span>
        <p class="text-gray-600 leading-relaxed text-[11px]">
            Memerlukan panduan teknis pengajuan OJS 3, sinkronisasi profil SINTA, atau verifikasi akun ORCID ID? Tim redaksi siap melayani pada hari kerja.
        </p>

        <div class="pt-2 border-t border-gray-200 space-y-1 text-[11px] text-gray-700">
            <div class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <a href="mailto:{{ $helpResources['email'] ?? 'jurnal.brida@makassarkota.go.id' }}" class="font-bold text-brand-maroon hover:underline">
                    {{ $helpResources['email'] ?? 'jurnal.brida@makassarkota.go.id' }}
                </a>
            </div>
            <div class="flex items-center gap-2 text-gray-500">
                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $helpResources['office_hours'] ?? 'Senin - Jumat: 08.00 - 16.00 WITA' }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Help Links -->
    <div class="space-y-2">
        <a href="#" class="w-full flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-800 transition-all">
            <span>Checklist Kesiapan Naskah</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <a href="#" class="w-full flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-800 transition-all">
            <span>Etika Publikasi & Plagiasi Turnitin</span>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
