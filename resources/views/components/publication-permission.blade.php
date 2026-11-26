@if(filled($license))
    @if($hasPermission ?? false)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 sm:p-5 flex items-start gap-3.5 shadow-2xs">
            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-xs sm:text-sm font-bold text-emerald-950">Izin Penerbitan Naskah Terverifikasi</h3>
                <p class="text-xs text-emerald-800 mt-0.5 leading-relaxed">
                    {{ $editor ? 'Persetujuan penerbitan naskah telah diverifikasi di bawah lisensi' : 'Anda telah memberikan izin penerbitan naskah ini di bawah lisensi terbuka' }}
                    <strong class="font-bold">{{ $license }}</strong>.
                </p>
            </div>
        </div>
    @else
        <form method="post" action="{{ route($editor ? 'eic.submissions.permission' : 'author.submissions.permission', $id) }}" class="rounded-xl border border-amber-200/90 bg-amber-50/40 p-5 sm:p-6 space-y-4 shadow-2xs">
            @csrf
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-sm font-bold text-gray-900 leading-snug">Izin penerbitan naskah</h2>
                    <p class="text-xs text-gray-600 mt-0.5 leading-relaxed">
                        {{ $editor 
                            ? 'Verifikasi perjanjian tertulis dari penulis sebelum naskah dijadwalkan dan diterbitkan resmi.' 
                            : 'Berdasarkan UU Hak Cipta, persetujuan lisensi distribusi terbuka diperlukan sebelum artikel ini diterbitkan secara publik.' }}
                    </p>
                </div>
            </div>

            <input type="hidden" name="license" value="{{ trim($license) }}">

            @if($editor)
                <div class="space-y-1.5 pt-1">
                    <label class="block text-xs font-bold text-gray-700">
                        Referensi perjanjian tertulis <span class="text-red-500">*</span>
                    </label>
                    <input 
                        required 
                        maxlength="255" 
                        name="agreement_reference" 
                        placeholder="Nomor atau kode arsip perjanjian (misal: ARSIP-2026-01)"
                        value="{{ old('agreement_reference') }}"
                        class="w-full max-w-md px-3.5 py-2 text-xs rounded-lg border border-gray-300 focus:border-brand-maroon focus:ring-1 focus:ring-brand-maroon bg-white text-gray-900 shadow-2xs transition"
                    >
                </div>
            @endif

            <div class="pt-1">
                <label class="flex items-start gap-2.5 cursor-pointer text-xs text-gray-800 leading-relaxed select-none">
                    <input 
                        required 
                        type="checkbox" 
                        name="confirmed" 
                        value="1"
                        class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-maroon focus:ring-brand-maroon cursor-pointer shrink-0"
                    >
                    <span>
                        {{ $editor ? 'Saya telah memverifikasi perjanjian penulis untuk menerbitkan naskah ini dengan lisensi' : 'Saya mengizinkan penerbitan naskah ini dengan lisensi' }}
                        <strong class="font-bold text-gray-900">{{ $license }}</strong>.
                    </span>
                </label>
            </div>

            @foreach(['confirmed', 'license', 'agreement_reference'] as $field)
                @error($field)
                    <p role="alert" class="text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            @endforeach

            <div class="pt-1 flex items-center justify-start">
                <button 
                    type="submit" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-brand-maroon hover:bg-brand-maroon/90 active:scale-98 rounded-lg shadow-2xs transition cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Catat izin penerbitan</span>
                </button>
            </div>
        </form>
    @endif
@endif
