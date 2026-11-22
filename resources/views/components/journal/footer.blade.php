@props(['footerData' => null])

<footer class="bg-white border-t border-gray-200 mt-10 sm:mt-12 text-xs text-gray-600 w-full max-w-full overflow-hidden">
    <div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
            <!-- Col 1: Brand Info -->
            <div class="space-y-3">
                <a href="/" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/brida-logo.png') }}" alt="Logo BRIDA Makassar" class="w-9 h-9 object-contain shrink-0 transition-transform group-hover:scale-105">
                    <span class="font-bold text-brand-maroon text-base block leading-tight">
                        {{ $footerData['publisher_name'] ?? 'BRIDA KOTA MAKASSAR' }}
                    </span>
                </a>
                <p class="text-xs text-gray-500 leading-relaxed font-normal [text-wrap:pretty]">
                    {{ $footerData['description'] ?? 'Publikasi berkala ilmiah resmi Badan Riset dan Inovasi Daerah Kota Makassar yang berfokus pada hasil penelitian terapan, kebijakan publik, inovasi perkotaan, dan teknologi daerah.' }}
                </p>
                <div class="text-[11px] text-gray-400 space-y-0.5">
                    <p>p-ISSN: <strong class="font-medium text-gray-600">{{ $footerData['p_issn'] ?? '2985-789X' }}</strong></p>
                    <p>e-ISSN: <strong class="font-medium text-gray-600">{{ $footerData['e_issn'] ?? '2985-8888' }}</strong></p>
                </div>
            </div>

            <!-- Col 2: Sekretariat Redaksi -->
            <div class="space-y-2">
                <h4 class="font-bold text-gray-900 uppercase tracking-wider text-[11px] pb-1 border-b border-gray-100">Sekretariat Redaksi</h4>
                <p class="text-xs text-gray-500 leading-relaxed whitespace-pre-line font-normal break-words">
                    {{ $footerData['address'] ?? "Gedung Balai Kota Makassar, Lantai 3\nJl. Ahmad Yani No. 2, Kel. Baru, Kec. Ujung Pandang\nKota Makassar, Sulawesi Selatan 90111" }}
                </p>
                <div class="pt-1 space-y-1 text-xs text-gray-600 font-normal">
                    <p class="break-all">Email: <a href="mailto:{{ $footerData['email'] ?? 'jurnal.brida@makassarkota.go.id' }}" class="text-brand-maroon hover:underline font-semibold">{{ $footerData['email'] ?? 'jurnal.brida@makassarkota.go.id' }}</a></p>
                    <p>Telp: <span class="font-medium text-gray-800">{{ $footerData['phone'] ?? '(0411) 3622455' }}</span></p>
                </div>
            </div>

            <!-- Col 3: Pengindeksan & Lisensi -->
            <div class="space-y-2 sm:col-span-2 md:col-span-1">
                <h4 class="font-bold text-gray-900 uppercase tracking-wider text-[11px] pb-1 border-b border-gray-100">Pengindeksan & Lisensi</h4>
                <p class="text-xs text-gray-500 leading-relaxed font-normal [text-wrap:pretty]">
                    Artikel dalam jurnal ini diindeks oleh: SINTA (Akreditasi Peringkat 2), Garuda, CrossRef (DOI), Google Scholar, dan Directory of Open Access Journals (DOAJ).
                </p>
                <p class="text-[11px] text-gray-400 leading-relaxed pt-1 font-normal">
                    Lisensi jurnal: <strong class="text-gray-600 font-medium">{{ $footerData['license_info'] ?? 'Lisensi belum ditetapkan' }}</strong>.
                </p>
            </div>
        </div>
    </div>

    <!-- Bottom Copyright Bar -->
    <div class="bg-gray-50 border-t border-gray-200/80 py-3.5 px-3.5 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2.5 text-[11px] text-gray-500 text-center sm:text-left">
            <p>&copy; {{ date('Y') }} {{ $footerData['copyright'] ?? 'Badan Riset dan Inovasi Daerah Kota Makassar. Seluruh hak cipta dilindungi undang-undang.' }}</p>
            <div class="flex flex-wrap items-center justify-center sm:justify-end gap-2.5 sm:gap-4 font-normal text-gray-600">
                <a href="{{ route('journal.about.policies') }}" class="hover:text-brand-maroon transition-colors">Kebijakan Privasi</a>
                <span>•</span>
                <a href="{{ route('journal.about.policies') }}" class="hover:text-brand-maroon transition-colors">Etika Publikasi</a>
                <span>•</span>
                <a href="{{ route('journal.about') }}" class="hover:text-brand-maroon transition-colors">Tentang Jurnal</a>
            </div>
        </div>
    </div>
</footer>
