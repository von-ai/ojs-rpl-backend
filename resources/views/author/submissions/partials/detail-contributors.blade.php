<!-- Panel Daftar Penulis & Kontributor Naskah -->
<section class="bg-white rounded-2xl border border-slate-200/80 shadow-[0px_2px_3px_-1px_rgba(0,0,0,0.06),0px_1px_0px_0px_rgba(25,28,33,0.02),0px_0px_0px_1px_rgba(25,28,33,0.06)] p-5 sm:p-7 space-y-5">
    <!-- Header Section -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
        <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 leading-tight">
                    Penulis & Tim Kontributor
                </h2>
                <p class="text-[11px] text-slate-500 hidden sm:block">Daftar kepenulisan resmi dan afiliasi akademik</p>
            </div>
        </div>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold text-slate-600 bg-slate-100 border border-slate-200 tabular-nums">
            {{ count($submission['contributors']) }} Penulis Terdaftar
        </span>
    </div>

    <!-- Daftar Kartu Kontributor -->
    <div class="space-y-3">
        @foreach($submission['contributors'] as $author)
            @php
                // Inisial Nama untuk Avatar
                $parts = explode(' ', trim(preg_replace('/^(Dr\.|Prof\.|Ir\.|M\.Si\.|Ph\.D\.|M\.Kom\.|S\.Kom\.)\s*/i', '', $author['name'])));
                $initials = count($parts) >= 2 
                    ? strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1))
                    : strtoupper(substr($parts[0] ?? 'P', 0, 2));
            @endphp
            <div class="p-4 rounded-xl bg-slate-50/70 hover:bg-slate-50 border border-slate-200/80 transition duration-150 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                <div class="flex items-start sm:items-center gap-3.5 min-w-0 flex-1">
                    <!-- Avatar Inisial -->
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-maroon to-red-950 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                        {{ $initials }}
                    </div>

                    <div class="min-w-0 flex-1 space-y-1">
                        <!-- Nama & Badge Korespondensi -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-mono font-bold text-slate-400 tabular-nums">#{{ $author['position'] }}</span>
                            <h3 class="text-sm font-bold text-slate-900 leading-snug">
                                {{ $author['name'] }}
                            </h3>
                            @if($author['is_corresponding'])
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                    <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>Penulis Korespondensi</span>
                                </span>
                            @endif
                        </div>

                        <!-- Afiliasi Institusi -->
                        <p class="text-xs text-slate-600 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="truncate">{{ $author['affiliation'] }}</span>
                        </p>

                        <!-- Strip Kontak & ORCID -->
                        <div class="flex items-center gap-3.5 text-xs text-slate-500 pt-0.5 flex-wrap">
                            <!-- Email dengan Klik Salin -->
                            <button 
                                type="button" 
                                onclick="copyTextToClipboard('{{ $author['email'] }}', 'Alamat email {{ $author['email'] }} disalin!')"
                                class="inline-flex items-center gap-1.5 text-slate-600 hover:text-brand-maroon transition cursor-pointer"
                                title="Klik untuk menyalin email"
                            >
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="hover:underline">{{ $author['email'] }}</span>
                            </button>

                            <!-- ORCID Link -->
                            @if(!empty($author['orcid']))
                                <a 
                                    href="https://orcid.org/{{ $author['orcid'] }}" 
                                    target="_blank" 
                                    rel="noopener noreferrer" 
                                    class="inline-flex items-center gap-1.5 text-[#A6CE39] hover:text-[#88B025] hover:underline font-mono text-xs font-semibold"
                                    title="Profil Terverifikasi ORCID (Open Researcher and Contributor ID)"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 256 256">
                                        <path d="M128,0A128,128,0,1,0,256,128,128,128,0,0,0,128,0ZM86.35,186.29H65.74V76.88H86.35Zm-10.3-120a11.94,11.94,0,1,1,11.94-11.94A11.94,11.94,0,0,1,76.05,66.29Zm119.5,73.18c0,27.67-17.78,46.82-47.53,46.82H110.1V76.88h41.05C178.65,76.88,195.55,97.77,195.55,139.47Zm-20.61,0c0-21.75-9.35-34.82-27.42-34.82h-17.5v69.64h17.5C165.59,174.29,174.94,161.22,174.94,139.47Z"/>
                                    </svg>
                                    <span>{{ $author['orcid'] }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
