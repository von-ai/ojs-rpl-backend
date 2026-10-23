<!-- Panel Daftar Penulis & Kontributor Naskah -->
<section class="bg-white rounded-xl border border-gray-200 p-6 sm:p-7 space-y-5">
    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-gray-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 rounded-md bg-brand-maroon/10 text-brand-maroon flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900 leading-tight">
                    Penulis & Tim Kontributor
                </h2>
                <p class="text-[11px] text-gray-500 hidden sm:block">Daftar kepenulisan resmi dan afiliasi akademik</p>
            </div>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200 tabular-nums">
            {{ count($submission['contributors']) }} Penulis Terdaftar
        </span>
    </div>

    <!-- Daftar Penulis & Kontributor -->
    <div class="divide-y divide-gray-100">
        @foreach($submission['contributors'] as $author)
            <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0 flex-1 space-y-0.5">
                    <!-- Nama & Badge Korespondensi -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-sans font-medium text-gray-400 tabular-nums">{{ $author['position'] }}.</span>
                        <h3 class="text-sm font-bold text-gray-900 leading-snug">
                            {{ $author['name'] }}
                        </h3>
                        @if($author['is_corresponding'])
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>Penulis Korespondensi</span>
                            </span>
                        @endif
                    </div>

                    <!-- Afiliasi Institusi -->
                    <p class="text-xs text-gray-500 truncate">
                        {{ $author['affiliation'] }}
                    </p>
                </div>

                <!-- Kontak & ORCID (Warna Netral & Font Bersih) -->
                <div class="flex items-center gap-3 sm:gap-4 text-xs text-gray-500 shrink-0 flex-wrap">
                    <!-- Email dengan Klik Salin -->
                    <button 
                        type="button" 
                        onclick="copyTextToClipboard('{{ $author['email'] }}', 'Alamat email {{ $author['email'] }} disalin!')"
                        class="inline-flex items-center gap-1.5 text-gray-600 hover:text-brand-maroon transition cursor-pointer"
                        title="Klik untuk menyalin email"
                    >
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="hover:underline">{{ $author['email'] }}</span>
                    </button>

                    <!-- ORCID Link Netral & Clean -->
                    @if(!empty($author['orcid']))
                        <a 
                            href="https://orcid.org/{{ $author['orcid'] }}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 hover:text-gray-900 transition font-sans text-xs font-medium shrink-0"
                            title="Identitas ORCID Terverifikasi"
                        >
                            <span class="text-gray-400 font-normal">ORCID:</span>
                            <span class="tabular-nums">{{ $author['orcid'] }}</span>
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
