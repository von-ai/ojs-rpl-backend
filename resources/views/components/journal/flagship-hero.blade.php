@props([
    'slides' => [],
    'journal' => null,
    'currentIssue' => null,
    'indexingData' => [],
])

@php
    $slideItems = ! empty($slides) ? $slides : [[
        'name' => $journal?->name ?: 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)',
        'initials' => $journal?->initials ?: 'SIRIDA',
        'publisher' => $journal?->publisher ?: 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
        'issn' => $journal?->issn ?: '2746-8194',
        'eissn' => $journal?->eissn ?: '2746-8208',
        'cover_url' => $journal?->coverImageUrl(),
        'volume_label' => $currentIssue['volume_label'] ?? null,
        'issue_title' => $currentIssue['theme_title'] ?? ($currentIssue['title'] ?? null),
        'description' => $journal?->focus_scope ?: 'Publikasi ilmiah berkala yang memuat hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, dan pembangunan daerah berkelanjutan.',
        'is_paid' => $journal ? $journal->isPaidApc() : false,
        'apc_label' => ($journal && $journal->isPaidApc()) ? ($journal->apc_fee ?: 'Berbayar') : 'Rp 0 (Open Access)',
        'issue_url' => route('journal.issues.current'),
        'journal_url' => route('journal.index'),
        'about_url' => route('journal.about'),
        'submissions_url' => route('journal.about.submissions'),
    ]];

    $totalSlides = count($slideItems);
    $accreditation = $indexingData['accreditation'] ?? 'Terakreditasi SINTA 2';
@endphp

<!-- Hero Editorial SIRIDA: Foto Arsitektur Riset + Scrim Gradien Merah Resmi BRIN/BRIDA -->
<section
    id="journal-hero-carousel"
    data-hero-banner
    class="relative bg-[#1E1214] text-white border-b border-black/15 overflow-hidden"
    aria-label="Pencarian artikel dan sorotan edisi jurnal SIRIDA"
>
    <!-- Latar Foto Arsitektur Perpustakaan Riset (Jelas & Alami) -->
    <img
        src="{{ asset('images/hero-sirida-bg.jpg') }}"
        alt=""
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 w-full h-full object-cover object-center"
    >
    <!-- Scrim Arah Kiri-ke-Kanan Bernuansa Merah Marun Lembut (Teks Kiri Kontras Tinggi, Foto Kanan Terlihat Jelas) -->
    <div
        class="pointer-events-none absolute inset-0 bg-linear-to-r from-[#2B1014]/88 via-[#5B141D]/65 to-[#1F1316]/35"
        aria-hidden="true"
    ></div>
    <!-- Vignette Halus Atas & Bawah -->
    <div
        class="pointer-events-none absolute inset-0 bg-linear-to-t from-[#1A0C0F]/55 via-transparent to-[#1A0C0F]/25"
        aria-hidden="true"
    ></div>

    <div class="relative z-10 max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-11 lg:py-12 space-y-8 sm:space-y-9">

        <!-- 1. Panggung Editorial 2-Kolom (Kiri: Tipografi Inter Kontras Tinggi, Kanan: Sampul Jurnal + Pratinjau Berikutnya) -->
        <div class="relative">
            @foreach($slideItems as $idx => $slide)
                @php
                    $nextSlide = $totalSlides > 1 ? $slideItems[($idx + 1) % $totalSlides] : null;
                @endphp
                <div
                    data-hero-slide="{{ $idx }}"
                    class="{{ $idx === 0 ? 'grid' : 'hidden' }} grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center"
                >
                    <!-- Kiri: Tipografi & CTA (7 cols) -->
                    <div class="lg:col-span-7 space-y-5 order-2 lg:order-1">
                        <!-- Kontrol Play/Pause & Kicker -->
                        <div class="flex flex-wrap items-center gap-3">
                            @if($totalSlides > 1)
                                <button
                                    type="button"
                                    data-hero-toggle
                                    aria-label="Jeda atau lanjutkan rotasi otomatis"
                                    aria-pressed="false"
                                    class="w-9 h-9 rounded-lg border border-white/35 bg-black/20 hover:bg-black/35 text-white inline-flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                                >
                                    <svg data-icon-pause class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect x="6" y="5" width="4" height="14" rx="1" />
                                        <rect x="14" y="5" width="4" height="14" rx="1" />
                                    </svg>
                                    <svg data-icon-play class="w-3.5 h-3.5 hidden" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </button>
                                <span class="text-xs font-mono font-semibold text-white/85 tabular-nums">
                                    {{ str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) }} / {{ str_pad((string) $totalSlides, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="text-white/35" aria-hidden="true">|</span>
                            @endif

                            <span class="text-xs font-bold uppercase tracking-wider text-white/95">
                                @if(!empty($slide['volume_label']))
                                    Edisi Terkini &middot; {{ $slide['volume_label'] }}
                                @else
                                    {{ $accreditation }} &middot; Open Access
                                @endif
                            </span>
                        </div>

                        <!-- Judul Utama (Inter Bold) & Subjudul Edisi -->
                        <div class="space-y-2">
                            <h1 class="font-sans text-2xl sm:text-3xl lg:text-[38px] font-extrabold text-white leading-[1.15] tracking-tight text-left [text-wrap:balance]">
                                <a href="{{ $slide['journal_url'] }}" class="hover:text-white/90 transition-colors focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white rounded-xs">
                                    {{ $slide['name'] }}
                                </a>
                            </h1>

                            @if(!empty($slide['issue_title']))
                                <p class="text-base sm:text-lg lg:text-xl font-semibold text-white/90 leading-snug text-left">
                                    {{ $slide['issue_title'] }}
                                </p>
                            @else
                                <p class="text-base sm:text-lg font-medium text-white/90 leading-snug text-left">
                                    Publikasi ilmiah terbuka untuk riset dan inovasi daerah.
                                </p>
                            @endif
                        </div>

                        <!-- Ringkasan & Metadata ISSN -->
                        <div class="space-y-2.5 max-w-xl">
                            <p class="text-sm sm:text-[15px] text-white/85 leading-relaxed text-left line-clamp-2">
                                {{ $slide['description'] }}
                            </p>
                            <p class="text-xs font-mono text-white/85 tabular-nums">
                                p-ISSN <strong class="text-white font-semibold">{{ $slide['issn'] }}</strong>
                                &nbsp;&middot;&nbsp;
                                e-ISSN <strong class="text-white font-semibold">{{ $slide['eissn'] }}</strong>
                                &nbsp;&middot;&nbsp;
                                <span class="font-sans font-semibold text-white">{{ $slide['apc_label'] }}</span>
                            </p>
                        </div>

                        <!-- CTA Utama & Tautan Sekunder -->
                        <div class="pt-1 flex flex-wrap items-center gap-3.5">
                            <a
                                href="{{ $slide['issue_url'] }}"
                                class="min-h-11 px-6 py-2.5 rounded-lg bg-white hover:bg-gray-100 text-sm font-bold text-brand-maroon-hover inline-flex items-center justify-center gap-2 shadow-sm transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            >
                                <span>Lihat Edisi Terkini</span>
                            </a>

                            <a
                                href="{{ $slide['submissions_url'] }}"
                                class="min-h-11 px-5 py-2.5 rounded-lg bg-black/20 hover:bg-black/35 border border-white/40 text-sm font-semibold text-white inline-flex items-center justify-center gap-1.5 transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                            >
                                <span>Panduan &amp; Template</span>
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Kanan: Galeri Sampul Jurnal + Pratinjau Mengintip di Belakang (5 cols) -->
                    <div class="lg:col-span-5 flex items-center justify-center lg:justify-end order-1 lg:order-2">
                        <div class="relative flex items-center justify-center pr-6 sm:pr-12">
                            @if($nextSlide)
                                <button
                                    type="button"
                                    data-hero-next-peek
                                    aria-label="Tampilkan edisi berikutnya: {{ $nextSlide['name'] }}"
                                    class="hidden sm:block absolute right-0 top-1/2 -translate-y-1/2 scale-[0.84] opacity-75 hover:opacity-100 hover:translate-x-1 transition-all duration-300 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white rounded-lg"
                                    title="Berikutnya: {{ $nextSlide['name'] }}"
                                >
                                    <x-journal.cover-card
                                        :journal="$nextSlide"
                                        :issue="['volume_label' => $nextSlide['volume_label']]"
                                        size="lg"
                                    />
                                </button>
                            @endif

                            <a
                                href="{{ $slide['issue_url'] }}"
                                class="relative z-10 block transition-transform duration-300 hover:-translate-y-1 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white rounded-lg"
                                title="Buka {{ $slide['name'] }}"
                            >
                                <x-journal.cover-card
                                    :journal="$slide"
                                    :issue="['volume_label' => $slide['volume_label']]"
                                    size="xl"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Navigasi Bawah Carousel (Hanya Tampil Jika > 1 Slide) -->
            @if($totalSlides > 1)
                <div class="mt-8 pt-5 border-t border-white/20 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2" role="tablist" aria-label="Pilih sorotan edisi jurnal">
                        @foreach($slideItems as $idx => $slide)
                            <button
                                type="button"
                                data-hero-dot="{{ $idx }}"
                                aria-label="Tampilkan {{ $slide['name'] }}"
                                aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"
                                class="h-2 rounded-full transition-all cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white {{ $idx === 0 ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/70' }}"
                            ></button>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            id="hero-prev-btn"
                            aria-label="Edisi jurnal sebelumnya"
                            class="w-11 h-11 rounded-lg border border-white/35 bg-black/20 hover:bg-black/35 text-white inline-flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            id="hero-next-btn"
                            aria-label="Edisi jurnal berikutnya"
                            class="w-11 h-11 rounded-lg border border-white/35 bg-black/20 hover:bg-black/35 text-white inline-flex items-center justify-center transition cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- 2. Bilah Pencarian Artikel di Bawah Sorotan Jurnal (Outlined + Tombol Cari Tegas) -->
        <div class="pt-6 border-t border-white/20">
            <form
                action="{{ route('journal.articles.search') }}"
                method="GET"
                role="search"
                aria-label="Cari artikel ilmiah SIRIDA"
                class="max-w-3xl flex flex-col sm:flex-row items-stretch gap-2 bg-black/40 p-1.5 rounded-xl border border-white/55 hover:border-white/80 focus-within:border-white focus-within:bg-black/55 transition"
            >
                <div class="relative flex-1 min-w-0">
                    <svg class="w-5 h-5 text-white/85 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        aria-label="Cari judul artikel, nama penulis, DOI, atau kata kunci"
                        placeholder="Cari artikel ilmiah, nama penulis, DOI, atau topik riset..."
                        class="w-full min-h-12 pl-11 pr-4 py-2.5 text-sm sm:text-base bg-transparent border-0 rounded-lg focus:outline-none focus:ring-0 text-white placeholder:text-white/75"
                    >
                </div>
                <button
                    type="submit"
                    class="min-h-12 px-6 sm:px-7 py-2.5 rounded-lg bg-brand-maroon hover:bg-brand-maroon-hover active:bg-brand-maroon-active text-sm sm:text-base font-semibold text-white shadow-sm transition inline-flex items-center justify-center gap-2 shrink-0 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Cari Artikel</span>
                </button>
            </form>
        </div>
    </div>
</section>

@if($totalSlides > 1)
    <script>
        (function () {
            const root = document.getElementById('journal-hero-carousel');
            if (!root) return;

            const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
            const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));
            const toggleBtns = Array.from(root.querySelectorAll('[data-hero-toggle]'));
            const peekBtns = Array.from(root.querySelectorAll('[data-hero-next-peek]'));
            const prevBtn = document.getElementById('hero-prev-btn');
            const nextBtn = document.getElementById('hero-next-btn');
            if (slides.length <= 1) return;

            let current = 0;
            let timer = null;
            let isPaused = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function syncToggleIcons() {
                toggleBtns.forEach((btn) => {
                    btn.setAttribute('aria-pressed', isPaused ? 'true' : 'false');
                    btn.querySelector('[data-icon-pause]')?.classList.toggle('hidden', isPaused);
                    btn.querySelector('[data-icon-play]')?.classList.toggle('hidden', !isPaused);
                });
            }

            function showSlide(index) {
                current = (index + slides.length) % slides.length;
                slides.forEach((el, idx) => {
                    const active = idx === current;
                    el.classList.toggle('grid', active);
                    el.classList.toggle('hidden', !active);
                    el.classList.remove('is-slide-entering');
                    if (active) {
                        void el.offsetWidth;
                        el.classList.add('is-slide-entering');
                    }
                });
                dots.forEach((dot, idx) => {
                    const active = idx === current;
                    dot.setAttribute('aria-selected', active ? 'true' : 'false');
                    dot.classList.toggle('w-8', active);
                    dot.classList.toggle('bg-white', active);
                    dot.classList.toggle('w-2.5', !active);
                    dot.classList.toggle('bg-white/40', !active);
                });
            }

            function startAutoplay() {
                stopAutoplay();
                if (isPaused) return;
                timer = window.setInterval(() => showSlide(current + 1), 6000);
            }

            function stopAutoplay() {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }
            }

            prevBtn?.addEventListener('click', () => {
                showSlide(current - 1);
                startAutoplay();
            });
            nextBtn?.addEventListener('click', () => {
                showSlide(current + 1);
                startAutoplay();
            });
            peekBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    showSlide(current + 1);
                    startAutoplay();
                });
            });
            dots.forEach((dot, idx) => {
                dot.addEventListener('click', () => {
                    showSlide(idx);
                    startAutoplay();
                });
            });
            toggleBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    isPaused = !isPaused;
                    syncToggleIcons();
                    if (isPaused) {
                        stopAutoplay();
                    } else {
                        startAutoplay();
                    }
                });
            });

            root.addEventListener('mouseenter', stopAutoplay);
            root.addEventListener('mouseleave', startAutoplay);
            root.addEventListener('focusin', stopAutoplay);
            root.addEventListener('focusout', startAutoplay);

            syncToggleIcons();
            startAutoplay();
        })();
    </script>
@endif
