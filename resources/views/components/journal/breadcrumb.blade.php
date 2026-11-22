@props(['crumbs' => []])

<nav class="flex items-center text-xs text-gray-500 gap-1.5 sm:gap-2 flex-wrap" aria-label="Breadcrumb">
    <a href="{{ route('journal.index') }}" class="hover:text-brand-maroon transition-colors">Beranda</a>
    @foreach($crumbs as $crumb)
        <span class="text-gray-400">/</span>
        @if(!empty($crumb['url']) && !$loop->last)
            <a href="{{ $crumb['url'] }}" class="hover:text-brand-maroon transition-colors truncate max-w-[150px] sm:max-w-xs">{{ $crumb['label'] }}</a>
        @else
            <span class="text-gray-900 font-medium truncate max-w-[220px] sm:max-w-md" aria-current="page">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</nav>
