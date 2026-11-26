@props([
    'issue',
    'actionLabel' => 'Buka Daftar Isi Naskah',
])

@php
    $isModel = $issue instanceof \App\Models\Issue;

    $id = $isModel ? $issue->id : ($issue['id'] ?? null);
    $volume = $isModel ? $issue->volume : ($issue['volume'] ?? null);
    $number = $isModel ? $issue->number : ($issue['number'] ?? null);
    $year = $isModel ? $issue->year : ($issue['year'] ?? null);
    $title = $isModel ? $issue->title : ($issue['title'] ?? '');
    $description = $isModel ? $issue->description : ($issue['description'] ?? null);
    $isCurrent = $isModel ? (bool) ($issue->is_current ?? false) : (bool) ($issue['is_current'] ?? false);
    $url = $isModel ? route('journal.issues.show', $id) : ($issue['url'] ?? ($id ? route('journal.issues.show', $id) : '#'));

    $totalArticles = $isModel
        ? ($issue->articles_count ?? ($issue->relationLoaded('articles') ? $issue->articles->where('status', 'published')->count() : $issue->articles()->where('status', 'published')->count()))
        : ($issue['total_articles'] ?? $issue['articles_count'] ?? 0);

    // Format tanggal terbitan berlokalitas Indonesia
    if ($isModel) {
        $dateObj = $issue->published_at ?? $issue->publication_date;
        $dateLabel = $dateObj ? $dateObj->locale('id')->translatedFormat('F Y') : ($year ? "Tahun {$year}" : '');
    } else {
        $dateLabel = $issue['year_month'] ?? ($year ? "Tahun {$year}" : '');
    }

    $volumeNo = ($volume !== null && $number !== null)
        ? "Vol. {$volume} No. {$number}"
        : ($issue['volume_no'] ?? ($volume ? "Vol. {$volume}" : 'Edisi'));

    if (! $year && ! empty($dateLabel)) {
        if (preg_match('/\b(20\d\d)\b/', $dateLabel, $m)) {
            $year = $m[1];
        }
    }
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-outline-variant p-4 sm:p-5 hover:border-brand-maroon/40 transition-all flex flex-col justify-between space-y-3 sm:space-y-3.5 group shadow-none']) }}>
    <div class="space-y-2.5">
        <!-- Top Status & Year Badge -->
        <div class="flex items-center justify-between text-xs">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $isCurrent ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-700 border border-outline-variant' }}">
                {{ $isCurrent ? 'Edisi Terkini' : 'Arsip' }}
            </span>
            @if($year)
                <span class="text-xs text-gray-400 font-mono">Tahun {{ $year }}</span>
            @endif
        </div>

        <!-- Visual Box: Emblem Buku + Volume & Bulan -->
        <div class="bg-gray-50 group-hover:bg-red-50/40 p-3 sm:p-3.5 rounded-lg text-center border border-outline-variant transition-colors">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-brand-maroon mx-auto mb-1 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="font-bold text-gray-900 text-sm block leading-tight">{{ $volumeNo }}</span>
            <span class="text-xs text-gray-500 font-normal block mt-0.5">{{ $dateLabel }}</span>
        </div>

        <!-- Judul Edisi -->
        <h3 class="font-semibold text-gray-900 text-sm group-hover:text-brand-maroon transition-colors line-clamp-2 leading-snug [text-wrap:balance]">
            <a href="{{ $url }}">{{ $title }}</a>
        </h3>

        <!-- Deskripsi Edisi (Opsional) -->
        @if(!empty($description))
            <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed [text-wrap:pretty]">
                {{ $description }}
            </p>
        @endif

        <!-- Jumlah Naskah Terpublikasi -->
        <div class="text-xs text-gray-500">
            <strong class="font-semibold text-gray-700 tabular-nums">{{ $totalArticles }}</strong> Naskah Artikel Terpublikasi
        </div>
    </div>

    <!-- Action Link (Konsisten Terletak di Sisi Kanan) -->
    <div class="pt-2.5 sm:pt-3 border-t border-divider flex items-center justify-end text-xs">
        <a href="{{ $url }}" class="font-semibold text-brand-maroon hover:underline flex items-center gap-1 group-hover:translate-x-0.5 transition-transform ml-auto">
            <span>{{ $actionLabel }}</span>
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
