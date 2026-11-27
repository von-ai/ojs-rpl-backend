@props([
    'eyebrow' => null,
    'title' => '',
    'description' => null,
    'crumbs' => [],
])

<section data-hero-banner class="relative bg-[#1E1214] text-white border-b border-black/15">
    <!-- Wrapper khusus latar agar foto terpotong rapi tanpa memotong dropdown di atasnya -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <img
            src="{{ asset('images/hero-sirida-bg.jpg') }}"
            alt=""
            class="w-full h-full object-cover object-center"
        >
        <div class="absolute inset-0 bg-linear-to-r from-[#2B1014]/88 via-[#5B141D]/65 to-[#1F1316]/35"></div>
        <div class="absolute inset-0 bg-linear-to-t from-[#1A0C0F]/50 via-transparent to-[#1A0C0F]/20"></div>
    </div>

    <div class="relative z-20 max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-7 sm:py-9 space-y-4">
        @if(!empty($crumbs))
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-white/80 flex-wrap">
                <a href="{{ route('journal.index') }}" class="hover:text-white transition">Beranda</a>
                @foreach($crumbs as $crumb)
                    <span class="text-white/40" aria-hidden="true">/</span>
                    @if(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="hover:text-white transition">{{ $crumb['label'] }}</a>
                    @else
                        <span class="text-white font-semibold">{{ $crumb['label'] }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <div class="max-w-3xl space-y-2">
            @if($eyebrow)
                <p class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-white/85">
                    {{ $eyebrow }}
                </p>
            @endif
            <h1 class="font-sans text-2xl sm:text-3xl font-extrabold text-white leading-tight tracking-tight text-left [text-wrap:balance]">
                {{ $heading ?? $title }}
            </h1>
            @if($description)
                <p class="text-sm sm:text-[15px] text-white/85 leading-relaxed text-left max-w-2xl">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if(isset($slot) && trim((string) $slot) !== '')
            <div class="pt-1">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
