<section class="relative overflow-hidden flex flex-col justify-between gap-4 rounded-xl border border-[#A00010]/70 bg-gradient-to-br from-brand-maroon via-[#D40015] to-[#A80010] p-5 sm:p-6 text-white shadow-sm">
    <!-- Aksen Geometris Halus -->
    <div class="pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full bg-white/10" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-12 -left-8 h-32 w-32 rounded-full bg-black/10" aria-hidden="true"></div>

    <div class="relative space-y-2.5">
        <span class="inline-flex items-center gap-1.5 rounded-md border border-white/25 bg-white/15 px-2.5 py-1 text-xs font-semibold tracking-wide text-white">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span>Penerimaan Naskah</span>
        </span>

        <h2 class="text-xl sm:text-2xl font-bold text-white leading-snug">
            Untuk penulis
        </h2>

        <p class="text-sm text-white/95 leading-relaxed">
            Siapkan naskah sesuai panduan sebelum mengirimnya ke redaksi.
        </p>
    </div>

    <div class="relative space-y-2.5 pt-1">
        <a
            href="{{ route(auth()->user()?->hasRole('author') ? 'author.submissions.create' : (auth()->check() ? 'dashboard' : 'login'), absolute: false) }}"
            class="w-full min-h-12 inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm sm:text-base font-bold text-brand-maroon hover:bg-gray-50 active:scale-[0.99] shadow-xs transition cursor-pointer"
        >
            <svg class="h-4.5 w-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Kirim naskah</span>
        </a>

        <a
            href="{{ route('journal.about.submissions') }}"
            class="w-full min-h-11 inline-flex items-center justify-center gap-2 rounded-lg border border-white/40 bg-white/10 hover:bg-white/20 active:scale-[0.99] px-4 py-2 text-sm font-semibold text-white transition cursor-pointer"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span>Panduan penulis</span>
        </a>
    </div>

    <div class="relative pt-3 border-t border-white/20 flex items-center justify-between gap-2 text-xs text-white/95 font-medium">
        <span>Review: <strong class="font-bold text-white">3–4 Minggu</strong></span>
        <span class="text-white/45" aria-hidden="true">•</span>
        <span class="font-bold text-white">Bebas Biaya (Rp 0)</span>
    </div>
</section>
