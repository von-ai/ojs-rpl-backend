@extends('layouts.public', ['topbarData' => $viewModel->topbarData(), 'navLinks' => $viewModel->navLinks(), 'footerData' => $viewModel->footerData()])

@section('title', $article->title . ' — SIRIDA BRIDA Kota Makassar')

@push('meta')
    <!-- Google Scholar Meta Tags for Academic Indexing -->
    <meta name="citation_title" content="{{ $article->title }}">
    @foreach($article->contributors as $contributor)
        <meta name="citation_author" content="{{ $contributor->full_name }}">
        <meta name="citation_author_institution" content="{{ $contributor->affiliation }}">
    @endforeach
    <meta name="citation_publication_date" content="{{ $article->published_at ? $article->published_at->format('Y/m/d') : date('Y/m/d') }}">
    <meta name="citation_journal_title" content="{{ $article->journal?->name ?? 'Jurnal Riset dan Inovasi Daerah (SIRIDA)' }}">
    @if($article->issues->first())
        <meta name="citation_volume" content="{{ $article->issues->first()->volume }}">
        <meta name="citation_issue" content="{{ $article->issues->first()->number }}">
    @endif
    <meta name="citation_doi" content="10.55981/sirida.{{ $article->slug }}">
    @if($article->galleys->first())
        <meta name="citation_pdf_url" content="{{ route('journal.articles.galleys.download', ['slugOrId' => $article->slug, 'galleyId' => $article->galleys->first()->id]) }}">
    @endif

    <!-- Dublin Core (DC) Metadata -->
    <meta name="DC.Title" content="{{ $article->title }}">
    @foreach($article->contributors as $contributor)
        <meta name="DC.Creator.PersonalName" content="{{ $contributor->full_name }}">
    @endforeach
    <meta name="DC.Subject" content="{{ is_array($article->keywords) ? implode(', ', $article->keywords) : '' }}">
    <meta name="DC.Description" content="{{ Str::limit($article->abstract, 500) }}">
    <meta name="DC.Publisher" content="Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar">
    <meta name="DC.Date.created" scheme="ISO8601" content="{{ $article->created_at ? $article->created_at->toIso8601String() : '' }}">
    <meta name="DC.Date.issued" scheme="ISO8601" content="{{ $article->published_at ? $article->published_at->toIso8601String() : '' }}">
    <meta name="DC.Type" content="Text.Serial.Journal">
    <meta name="DC.Format" scheme="IMT" content="application/pdf">
    <meta name="DC.Identifier" content="{{ $article->slug }}">
    <meta name="DC.Identifier.URI" content="{{ url()->current() }}">
    <meta name="DC.Language" scheme="ISO639-1" content="id">
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">

    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center text-xs text-gray-500 gap-2 flex-wrap">
        <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon">Beranda</a>
        <span>/</span>
        <a href="{{ route('journal.articles.search') }}" class="hover:text-brand-maroon">Katalog Artikel</a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate max-w-md">{{ $article->title }}</span>
    </nav>

    <!-- Grid 2 Kolom (Konten Artikel & Sidebar Info) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Kolom Konten Utama (2/3) -->
        <article class="lg:col-span-2 space-y-8 bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
            
            <!-- Metadata Edisi & Kategori -->
            <div class="space-y-3 border-b border-gray-100 pb-6">
                <div class="flex items-center gap-2 flex-wrap text-xs">
                    <span class="px-2.5 py-0.5 rounded-full font-semibold bg-brand-maroon/10 text-brand-maroon">
                        {{ $article->section?->name ?? 'Artikel Riset Orisinil' }}
                    </span>
                    @if($article->issues->first())
                        <span class="text-gray-400">•</span>
                        <span class="text-gray-600 font-medium">
                            Vol. {{ $article->issues->first()->volume }} No. {{ $article->issues->first()->number }} ({{ $article->issues->first()->year }})
                        </span>
                    @endif
                    <span class="text-gray-400">•</span>
                    <span class="text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                        Open Access
                    </span>
                </div>

                <!-- Judul Artikel Ilmiah -->
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 leading-snug tracking-tight font-serif">
                    {{ $article->title }}
                </h1>

                <!-- DOI & Tanggal Terbit -->
                <div class="flex items-center gap-4 text-xs text-gray-500 flex-wrap pt-1">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-gray-700">DOI:</span>
                        <a href="https://doi.org/10.55981/sirida.{{ $article->slug }}" target="_blank" class="text-brand-maroon hover:underline font-mono">
                            https://doi.org/10.55981/sirida.{{ $article->slug }}
                        </a>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-gray-700">Diterbitkan:</span>
                        <span>{{ $article->published_at ? $article->published_at->format('d F Y') : date('d F Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Penulis & Afiliasi Institusi -->
            <div class="space-y-3 bg-gray-50/70 rounded-xl p-5 border border-gray-100">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Kontributor / Penulis:</h3>
                <div class="space-y-2">
                    @forelse($article->contributors as $contributor)
                        <div class="flex items-start gap-2 text-xs">
                            <div class="w-5 h-5 rounded-full bg-brand-maroon text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">
                                {{ substr($contributor->given_name, 0, 1) }}
                            </div>
                            <div>
                                <span class="font-bold text-gray-900">{{ $contributor->full_name }}</span>
                                <span class="text-gray-500 ml-1">({{ $contributor->affiliation }})</span>
                                @if($contributor->orcid)
                                    <a href="https://orcid.org/{{ $contributor->orcid }}" target="_blank" class="inline-flex items-center gap-0.5 text-emerald-600 hover:underline ml-1 font-mono text-[11px]">
                                        <span>ORCID: {{ $contributor->orcid }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">Penulis terdaftar di dewan redaksi BRIDA.</p>
                    @endforelse
                </div>
            </div>

            <!-- Abstrak Naskah -->
            <div class="space-y-3">
                <h2 class="text-sm sm:text-base font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2">
                    <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Abstrak</span>
                </h2>
                <div class="text-xs sm:text-sm text-gray-700 leading-relaxed text-justify space-y-2">
                    {!! nl2br(e($article->abstract)) !!}
                </div>
            </div>

            <!-- Kata Kunci (Keywords) -->
            @if(!empty($article->keywords))
                <div class="space-y-2 pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500">Kata Kunci:</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($article->keywords as $kw)
                            <a href="{{ route('journal.articles.search', ['q' => $kw]) }}" class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700 hover:bg-brand-maroon/10 hover:text-brand-maroon transition duration-150">
                                {{ $kw }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Daftar Pustaka (References) jika ada -->
            @if(!empty($article->references))
                <div class="space-y-3 pt-4 border-t border-gray-100">
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">Daftar Pustaka</h2>
                    <div class="text-xs text-gray-600 leading-relaxed space-y-1.5 font-mono whitespace-pre-line bg-gray-50 p-4 rounded-xl border border-gray-100">
                        {{ $article->references }}
                    </div>
                </div>
            @endif

            <!-- Generator Sitasi Ilmiah -->
            <div class="space-y-3 pt-4 border-t border-gray-100">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-maroon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                        <span>Sitasi Artikel Ini</span>
                    </h2>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('journal.articles.cite', ['slugOrId' => $article->slug, 'format' => 'bibtex']) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
                            Unduh BibTeX
                        </a>
                        <a href="{{ route('journal.articles.cite', ['slugOrId' => $article->slug, 'format' => 'ris']) }}" class="px-2.5 py-1 text-[11px] font-semibold rounded bg-gray-100 text-gray-700 hover:bg-gray-200">
                            Unduh RIS
                        </a>
                    </div>
                </div>

                <!-- Box Teks Sitasi APA -->
                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs font-mono text-gray-800 leading-relaxed select-all">
                    {{ $citations['apa'] ?? '' }}
                </div>
            </div>

        </article>

        <!-- Kolom Samping (Sidebar Aksi & Metrik) -->
        <aside class="lg:col-span-1 space-y-6">
            
            <!-- Card Unduh Galai PDF Utama -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Berkas Galai Resmi</h3>
                
                @if($article->galleys->isNotEmpty())
                    @foreach($article->galleys as $galley)
                        <a 
                            href="{{ route('journal.articles.galleys.download', ['slugOrId' => $article->slug, 'galleyId' => $galley->id]) }}" 
                            class="w-full inline-flex items-center justify-between p-3.5 rounded-xl border border-red-200 bg-red-50/60 hover:bg-red-100/70 active:scale-[0.98] transition group cursor-pointer"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-red-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <span class="block text-xs font-bold text-gray-900 group-hover:text-red-700">Unduh PDF Lengkap</span>
                                    <span class="text-[11px] text-gray-500">Format Resmi Publikasi</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </a>

                        <a 
                            href="{{ route('journal.articles.galleys.download', ['slugOrId' => $article->slug, 'galleyId' => $galley->id]) }}" 
                            target="_blank"
                            class="w-full inline-flex items-center justify-center gap-2 p-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold transition"
                        >
                            <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Buka Pembaca PDF</span>
                        </a>
                    @endforeach
                @else
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 text-center text-xs text-gray-500">
                        Berkas PDF galai belum diunggah untuk naskah ini.
                    </div>
                @endif

                <p class="text-[11px] text-gray-500 text-center leading-tight">
                    Lisensi artikel terbuka untuk disitasi, disebarluaskan, dan dimanfaatkan secara bebas untuk kemaslahatan publik.
                </p>
            </div>

            <!-- Card Statistik Metrik COUNTER -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-4 text-xs">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-3">Statistik Pembaca</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <span class="block text-lg font-extrabold text-gray-900 tabular-nums">
                            {{ $article->metrics()->where('event_type', 'abstract_view')->sum('count') ?: 1 }}
                        </span>
                        <span class="text-[11px] text-gray-500 font-medium">Kali Dilihat</span>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                        <span class="block text-lg font-extrabold text-brand-maroon tabular-nums">
                            {{ $article->metrics()->where('event_type', 'galley_download')->sum('count') ?: 0 }}
                        </span>
                        <span class="text-[11px] text-gray-500 font-medium">Kali Diunduh</span>
                    </div>
                </div>
            </div>

            <!-- Card Hak Cipta & Lisensi Open Access -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-3 text-xs text-gray-600">
                <h3 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">Lisensi Publikasi</h3>
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200">
                        <span class="font-bold text-[10px]">CC</span>
                    </div>
                    <p class="text-[11px] leading-relaxed">
                        Artikel ini dilisensikan di bawah <strong>{{ $journal->license ?? 'Creative Commons Attribution 4.0 International (CC BY 4.0)' }}</strong>. Hak cipta naskah tetap dipegang oleh penulis.
                    </p>
                </div>
            </div>

        </aside>

    </div>

</div>
@endsection
