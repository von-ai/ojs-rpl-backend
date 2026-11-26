@props(['topbarData' => null, 'journal' => null])

<div class="bg-gray-50 border-b border-divider text-xs py-1.5 px-3.5 sm:px-6 lg:px-8 w-full max-w-full overflow-hidden">
    <div class="max-w-7xl mx-auto flex flex-row items-center justify-between gap-2 text-gray-700 text-xs">
        <!-- Left Metadata -->
        <div class="flex items-center gap-1.5 sm:gap-2 truncate">
            <span><strong class="font-semibold text-gray-800">p-ISSN:</strong> {{ $topbarData['p_issn'] ?? '2985-789X' }}</span>
            <span class="text-gray-400 hidden sm:inline" aria-hidden="true">•</span>
            <span class="hidden sm:inline"><strong class="font-semibold text-gray-800">e-ISSN:</strong> {{ $topbarData['e_issn'] ?? '2985-8888' }}</span>
            @if(!empty($topbarData['journals']) && count($topbarData['journals']) > 1)
                <span class="text-gray-400 hidden sm:inline" aria-hidden="true">•</span>
                <a href="{{ route('journal.journals.index') }}" class="hidden sm:inline-flex items-center gap-1 font-semibold text-brand-maroon hover:underline" title="Lihat Direktori Seluruh Jurnal">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Katalog {{ count($topbarData['journals']) }} Jurnal</span>
                </a>
            @endif
            @if($journal && $journal->status !== 'active' && auth()->user()?->isSysAdmin())
                <span class="ml-1 hidden md:inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-900 border border-amber-300">
                    Pratinjau SysAdmin: Status {{ ucfirst($journal->status) }}
                </span>
            @endif
        </div>

        <!-- Right Quick Actions, Text Size & Auth -->
        <div class="flex items-center justify-end gap-2 sm:gap-3 shrink-0 text-xs">
            <!-- Kontrol Ukuran Teks Ramah Pembaca Senior -->
            <div class="inline-flex items-center rounded-md border border-gray-300 bg-white overflow-hidden" role="group" aria-label="Pengaturan ukuran teks">
                <button type="button" data-text-scale-btn="normal" aria-pressed="true" title="Ukuran teks normal" class="px-2 py-0.5 text-[11px] font-bold text-gray-700 hover:bg-gray-100 transition cursor-pointer">A</button>
                <button type="button" data-text-scale-btn="lg" aria-pressed="false" title="Ukuran teks besar" class="px-2 py-0.5 text-xs font-bold text-gray-700 hover:bg-gray-100 border-l border-divider transition cursor-pointer">A+</button>
                <button type="button" data-text-scale-btn="xl" aria-pressed="false" title="Ukuran teks sangat besar" class="hidden sm:inline-block px-2 py-0.5 text-[13px] font-bold text-gray-700 hover:bg-gray-100 border-l border-divider transition cursor-pointer">A++</button>
            </div>

            <span class="hidden sm:inline text-gray-300" aria-hidden="true">|</span>

            <!-- Auth Links -->
            @auth
                @if(auth()->user()->isSysAdmin())
                    <a href="{{ route('sysadmin.journals.index', absolute: false) }}" class="font-semibold text-brand-maroon hover:underline inline-flex items-center gap-1" title="Kembali ke Portal System Administrator">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Portal System Admin</span>
                        <span class="sm:hidden">Portal Admin</span>
                    </a>
                @else
                    <a href="{{ route('dashboard', absolute: false) }}" class="font-semibold text-brand-maroon hover:underline">
                        Dashboard Portal
                    </a>
                @endif
            @else
                <a href="{{ route('login', absolute: false) }}" class="font-semibold text-gray-700 hover:text-brand-maroon transition-colors">
                    Masuk
                </a>
                <span class="text-gray-400" aria-hidden="true">•</span>
                <a href="{{ route('register') }}" class="font-semibold text-gray-700 hover:text-brand-maroon transition-colors">
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</div>

@once
<script>
(function () {
    if (window.__siridaTextScaleInitialized) return;
    window.__siridaTextScaleInitialized = true;
    var KEY = 'sirida_text_scale';
    var SCALES = ['normal', 'lg', 'xl'];
    function getSaved() { try { return localStorage.getItem(KEY) || 'normal'; } catch (e) { return 'normal'; } }
    function apply(scale) {
        var v = SCALES.indexOf(scale) !== -1 ? scale : 'normal';
        var r = document.documentElement;
        r.classList.remove('text-scale-lg', 'text-scale-xl');
        r.style.fontSize = v === 'lg' ? '112.5%' : (v === 'xl' ? '125%' : '');
        if (v === 'lg') r.classList.add('text-scale-lg');
        if (v === 'xl') r.classList.add('text-scale-xl');
        try { localStorage.setItem(KEY, v); } catch (e) {}
        document.querySelectorAll('[data-text-scale-btn]').forEach(function (btn) {
            var t = btn.getAttribute('data-text-scale-btn');
            if (t === 'cycle') {
                var lbl = v === 'normal' ? 'A+' : (v === 'lg' ? 'A++' : 'A');
                var span = btn.querySelector('[data-text-scale-label]');
                if (span) span.textContent = lbl;
                btn.setAttribute('aria-pressed', v !== 'normal' ? 'true' : 'false');
                btn.classList.toggle('bg-red-50', v !== 'normal');
                btn.classList.toggle('text-brand-maroon', v !== 'normal');
                btn.classList.toggle('border-brand-maroon/40', v !== 'normal');
            } else {
                var active = t === v;
                btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                btn.classList.toggle('bg-brand-maroon', active);
                btn.classList.toggle('text-white', active);
                btn.classList.toggle('text-gray-700', !active);
            }
        });
    }
    window.siridaApplyTextScale = apply;
    apply(getSaved());
    document.addEventListener('DOMContentLoaded', function () { apply(getSaved()); });
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-text-scale-btn]');
        if (!btn) return;
        var t = btn.getAttribute('data-text-scale-btn');
        if (t === 'cycle') {
            var cur = getSaved();
            apply(SCALES[(SCALES.indexOf(cur) + 1) % SCALES.length]);
        } else {
            apply(t);
        }
    });
})();
</script>
@endonce
