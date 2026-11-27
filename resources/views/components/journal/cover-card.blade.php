@props([
    'journal' => null,
    'issue' => null,
    'size' => 'md', // sm | md | lg | xl
])

@php
    $coverUrl = is_object($journal) && method_exists($journal, 'coverImageUrl')
        ? $journal->coverImageUrl()
        : (is_array($journal) ? ($journal['cover_url'] ?? null) : null);

    $journalName = is_object($journal)
        ? ($journal->name ?? 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)')
        : ($journal['name'] ?? 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)');

    $initials = is_object($journal)
        ? ($journal->initials ?: 'SIRIDA')
        : ($journal['initials'] ?? 'SIRIDA');

    $publisher = is_object($journal)
        ? ($journal->publisher ?: 'BRIDA Kota Makassar')
        : ($journal['publisher'] ?? 'BRIDA Kota Makassar');

    $issn = is_object($journal) ? ($journal->issn ?? null) : ($journal['issn'] ?? null);
    $eissn = is_object($journal) ? ($journal->eissn ?? null) : ($journal['eissn'] ?? null);
    $volumeLabel = is_array($issue) ? ($issue['volume_label'] ?? null) : null;

    $sizeClasses = match ($size) {
        'sm' => 'w-14 h-[74px] rounded-r-sm rounded-l-[2px]',
        'lg' => 'w-40 sm:w-48 aspect-[3/4] rounded-r-lg rounded-l-[3px]',
        'xl' => 'w-44 sm:w-52 lg:w-56 aspect-[3/4] rounded-r-lg rounded-l-[3px]',
        default => 'w-28 sm:w-32 aspect-[3/4] rounded-r-lg rounded-l-[3px]',
    };

    $isBookLarge = in_array($size, ['lg', 'xl'], true);
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-block shrink-0 select-none font-sans']) }}>
    @if($isBookLarge)
        <!-- Lapisan Tebal Halaman Kertas di Sisi Kanan -->
        <div
            class="pointer-events-none absolute top-1.5 -right-2 bottom-1.5 w-2 rounded-r-[3px] bg-linear-to-r from-gray-200 via-gray-50 to-gray-300 border-y border-r border-black/15"
            aria-hidden="true"
        ></div>
    @endif

    <div class="relative overflow-hidden bg-brand-navy ring-1 ring-black/15 {{ $isBookLarge ? 'shadow-[0_18px_36px_-14px_rgba(17,24,39,0.38),0_4px_10px_-2px_rgba(17,24,39,0.14)]' : 'shadow-sm' }} {{ $sizeClasses }}">
        @if($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="Sampul {{ $journalName }}"
                loading="lazy"
                class="w-full h-full object-cover"
            >
            <div class="pointer-events-none absolute inset-y-0 left-0 w-2.5 bg-linear-to-r from-black/30 via-white/15 to-transparent" aria-hidden="true"></div>
        @else
            <!-- Sampul Cetak Standar SIRIDA (Tipografi Inter, Palet Resmi BRIN/BRIDA) -->
            <div class="relative w-full h-full flex flex-col justify-between bg-brand-navy text-white font-sans">
                <div class="pointer-events-none absolute inset-y-0 left-0 w-3 bg-linear-to-r from-black/35 via-white/10 to-transparent" aria-hidden="true"></div>
                <div class="pointer-events-none absolute inset-y-0 left-3 w-px bg-white/15" aria-hidden="true"></div>

                <!-- Pita Atas Merah BRIN -->
                <div class="{{ $isBookLarge ? 'h-2.5' : 'h-1.5' }} w-full bg-brand-maroon shrink-0" aria-hidden="true"></div>

                @if($size === 'sm')
                    <div class="flex-1 flex flex-col items-center justify-center px-1 text-center">
                        <span class="text-[10px] font-bold tracking-wider uppercase text-white leading-none">
                            {{ \Illuminate\Support\Str::limit($initials, 6, '') }}
                        </span>
                        <span class="mt-1 w-5 h-0.5 bg-brand-maroon"></span>
                    </div>
                @else
                    <div class="flex-1 flex flex-col justify-between {{ $isBookLarge ? 'p-4 pl-5 sm:p-5 sm:pl-6' : 'p-2.5 pl-4' }}">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between gap-2 border-b border-white/20 pb-2">
                                <span class="{{ $isBookLarge ? 'text-[11px]' : 'text-[9.5px]' }} font-bold tracking-[0.12em] uppercase text-white">
                                    {{ \Illuminate\Support\Str::limit($initials, 10, '') }}
                                </span>
                                @if($isBookLarge)
                                    <span class="text-[9px] font-semibold uppercase tracking-wider text-white/85">
                                        Open Access
                                    </span>
                                @endif
                            </div>

                            <p class="{{ $isBookLarge ? 'text-sm sm:text-[15px] leading-snug' : 'text-[10.5px] leading-tight' }} font-bold tracking-tight text-white line-clamp-5 text-left">
                                {{ $journalName }}
                            </p>

                            @if($isBookLarge)
                                <div class="w-8 h-0.5 bg-brand-maroon" aria-hidden="true"></div>
                            @endif
                        </div>

                        <div class="space-y-1 pt-2.5 border-t border-white/20 text-left">
                            @if($volumeLabel && $isBookLarge)
                                <p class="text-[10.5px] font-semibold text-white/95 truncate tabular-nums">
                                    {{ $volumeLabel }}
                                </p>
                            @endif
                            @if($eissn || $issn)
                                <p class="text-[9.5px] font-mono text-white/80 truncate tabular-nums">
                                    {{ $eissn ? "e-ISSN {$eissn}" : "ISSN {$issn}" }}
                                </p>
                            @endif
                            <p class="text-[8.5px] font-semibold uppercase tracking-wider text-white/70 truncate">
                                {{ $publisher }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
