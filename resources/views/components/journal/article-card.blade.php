@props(['article'])

@php
    $isModel = $article instanceof \App\Models\Article;

    if ($isModel) {
        $slugOrId = $article->slug ?: $article->id;
        $title = $article->title;
        $readUrl = route('journal.articles.show', $slugOrId);
        $sectionName = $article->section?->name;
        $pages = $article->pivot?->pages ?? ($article->pages ?? null);

        $publishedAtObj = $article->published_at;
        $publishedAt = $publishedAtObj ? $publishedAtObj->locale('id')->translatedFormat('d F Y') : null;

        if ($article->relationLoaded('contributors') && $article->contributors->isNotEmpty()) {
            $authors = $article->contributors->pluck('full_name')->filter()->values()->all();
            $affiliations = $article->contributors->pluck('affiliation')->filter()->unique()->values()->all();
        } else {
            $authors = [];
            $affiliations = [];
        }

        $abstract = strip_tags($article->abstract ?? '');
        $doi = $article->doi;
        $views = $article->views ?? null;
        $downloads = $article->downloads ?? null;

        $galleys = $article->relationLoaded('galleys') ? $article->galleys : collect();
        $downloadUrl = $galleys->first()
            ? route('journal.articles.galleys.download', ['slugOrId' => $slugOrId, 'galleyId' => $galleys->first()->id])
            : null;
    } else {
        $title = $article['title'] ?? '';
        $slugOrId = $article['slug'] ?? ($article['id'] ?? null);
        $readUrl = $article['read_url'] ?? ($article['url'] ?? ($slugOrId ? route('journal.articles.show', $slugOrId) : '#'));
        $sectionName = $article['section'] ?? null;
        $pages = $article['pages'] ?? null;
        $publishedAt = $article['published_at'] ?? null;
        $authors = (array) ($article['authors'] ?? []);
        $affiliations = (array) ($article['affiliations'] ?? []);
        $abstract = strip_tags($article['abstract'] ?? '');
        $doi = $article['doi'] ?? null;
        $views = $article['views_count'] ?? ($article['views'] ?? null);
        $downloads = $article['downloads_count'] ?? ($article['downloads'] ?? null);
        $downloadUrl = $article['download_url'] ?? ($article['pdf_url'] ?? null);

        if (! empty($article['galleys']) && is_iterable($article['galleys'])) {
            $galleys = $article['galleys'];
        } elseif (! empty($downloadUrl)) {
            $galleys = [['url' => $downloadUrl, 'label' => $article['galley_label'] ?? 'PDF']];
        } else {
            $galleys = [];
        }
    }

    if (empty($authors)) {
        $authors = ['Peneliti BRIDA'];
    }
@endphp

<div class="bg-white rounded-xl border border-gray-200/90 p-4 sm:p-5 hover:border-brand-maroon/40 transition-all space-y-3 group shadow-none">
    <!-- Top Metadata Badges & Dates -->
    <div class="flex flex-wrap items-center justify-between gap-2 text-xs border-b border-gray-100 pb-2.5">
        <div class="flex items-center flex-wrap gap-2">
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-800 border border-emerald-200 tracking-normal">
                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                Publik
            </span>

            @if(!empty($sectionName))
                <span class="text-gray-300">•</span>
                <span class="font-medium text-gray-700 text-xs">{{ $sectionName }}</span>
            @endif

            @if(!empty($pages))
                <span class="text-gray-300">•</span>
                <span class="text-gray-500 font-mono text-xs">Hal. {{ $pages }}</span>
            @endif
        </div>

        @if(!empty($publishedAt))
            <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                <span>Terbit: <strong class="font-medium text-gray-700">{{ $publishedAt }}</strong></span>
            </div>
        @endif
    </div>

    <!-- Article Title -->
    <h3 class="text-sm sm:text-base font-bold text-gray-900 group-hover:text-brand-maroon leading-snug break-words [text-wrap:balance] transition-colors">
        @if(!empty($readUrl) && $readUrl !== '#')
            <a href="{{ $readUrl }}">{{ $title }}</a>
        @else
            {{ $title }}
        @endif
    </h3>

    <!-- Authors & Affiliations -->
    <div class="space-y-0.5 text-xs">
        <div class="font-medium text-gray-800">
            {{ implode(', ', $authors) }}
        </div>
        @if(!empty($affiliations))
            <div class="text-gray-500 italic text-[11px]">
                {{ implode('; ', $affiliations) }}
            </div>
        @endif
    </div>

    <!-- Abstract Preview -->
    @if(!empty($abstract))
        <p class="text-xs text-gray-600 leading-relaxed line-clamp-2 sm:line-clamp-3 font-normal [text-wrap:pretty] break-words">
            {{ Str::limit($abstract, 240) }}
        </p>
    @endif

    <!-- Bottom Footer Row: DOI, Metrics & Action Buttons -->
    <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
        <!-- Left: DOI & Metrics -->
        <div class="flex items-center flex-wrap gap-2 sm:gap-3 text-gray-500 min-w-0">
            @if(!empty($doi))
                @php
                    $doiClean = preg_replace('#^https?://(dx\.)?doi\.org/#i', '', $doi);
                    $doiUrl = 'https://doi.org/' . $doiClean;
                @endphp
                <a href="{{ $doiUrl }}" target="_blank" rel="noopener noreferrer" class="text-[11px] text-gray-600 hover:text-brand-maroon underline break-all font-mono">
                    doi:{{ $doiClean }}
                </a>
                @if(($views !== null) || ($downloads !== null))
                    <span class="text-gray-300">•</span>
                @endif
            @endif

            @if($views !== null)
                <span class="flex items-center gap-1 text-[11px] tabular-nums">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ $views }} Dilihat
                </span>
            @endif

            @if($downloads !== null)
                @if($views !== null)
                    <span class="text-gray-300">•</span>
                @endif
                <span class="flex items-center gap-1 text-[11px] tabular-nums">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    {{ $downloads }} Unduhan
                </span>
            @endif
        </div>

        <!-- Right: Action Buttons (Lihat Abstrak + Unduh Galai PDF) -->
        <div class="flex items-center gap-2 w-full sm:w-auto justify-end pt-1 sm:pt-0 shrink-0">
            @if(!empty($readUrl) && $readUrl !== '#')
                <a href="{{ $readUrl }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:text-brand-maroon bg-gray-50 hover:bg-gray-100 active:scale-[0.98] border border-gray-200 rounded-lg transition-all">
                    <span>Lihat Abstrak</span>
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif

            @foreach($galleys as $g)
                @php
                    $gUrl = is_object($g)
                        ? route('journal.articles.galleys.download', ['slugOrId' => $slugOrId, 'galleyId' => $g->id])
                        : ($g['url'] ?? $downloadUrl);
                    $gLabel = is_object($g) ? ($g->label ?: 'PDF') : ($g['label'] ?? 'PDF');
                @endphp
                @if(!empty($gUrl) && $gUrl !== '#')
                    <a href="{{ $gUrl }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon-hover active:scale-[0.98] rounded-lg transition-all shadow-none">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>{{ $gLabel }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
