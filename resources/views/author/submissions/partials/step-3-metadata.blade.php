<!-- ========================================== -->
<!-- LANGKAH 3: METADATA NASKAH ILMIAH -->
<!-- ========================================== -->
<div id="step-panel-3" class="wizard-step-panel hidden bg-white rounded-xl border border-gray-200 p-5 sm:p-7 shadow-2xs space-y-6">
    <div class="border-b border-gray-100 pb-4">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-maroon">Langkah 3 dari 5</span>
        <h2 class="text-lg sm:text-xl font-bold text-gray-900 mt-1">Metadata Naskah Ilmiah</h2>
        <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
            Lengkapi judul artikel, abstrak lengkap, kata kunci terindeks, dan bidang fokus riset Anda.
        </p>
    </div>

    <!-- Judul Naskah -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between">
            <label for="title" class="block text-xs sm:text-sm font-bold text-gray-900">
                Judul Naskah Lengkap <span class="text-red-600">*</span>
            </label>
            <span class="text-[11px] text-gray-400 tabular-nums">
                <span id="title-word-count">0</span> / 20 kata
            </span>
        </div>
        <textarea 
            name="title" 
            id="title" 
            rows="2" 
            required
            placeholder="Contoh: Analisis Efektivitas Layanan Transportasi Publik Berkelanjutan di Kota Makassar..."
            class="w-full p-3 sm:p-3.5 text-xs sm:text-sm rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition font-medium"
        >{{ old('title') }}</textarea>
    </div>

    <!-- Abstrak Naskah -->
    <div class="space-y-1.5">
        <div class="flex items-center justify-between">
            <label for="abstract" class="block text-xs sm:text-sm font-bold text-gray-900">
                Abstrak Naskah <span class="text-red-600">*</span>
            </label>
            <span class="text-[11px] text-gray-400 tabular-nums">
                <span id="abstract-word-count">0</span> / 250 kata
            </span>
        </div>
        <textarea 
            name="abstract" 
            id="abstract" 
            rows="6" 
            required
            placeholder="Tuliskan latar belakang singkat, tujuan riset, metode, temuan utama, dan kontribusi kebijakan..."
            class="w-full p-3.5 text-xs sm:text-sm rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition leading-relaxed font-sans"
        >{{ old('abstract') }}</textarea>
    </div>

    <!-- Kata Kunci (Interactive Tags Input) -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="block text-xs sm:text-sm font-bold text-gray-900">
                Kata Kunci <span class="text-red-600">*</span>
            </label>
            <span class="text-[11px] text-gray-400 tabular-nums">
                <span id="keywords-count">0</span> / 20 kata kunci
            </span>
        </div>
        
        <!-- Container Tag Chips -->
        <div id="keywords-container" class="min-h-11 p-2 rounded-xl border border-gray-300 bg-white flex flex-wrap items-center gap-1.5 focus-within:border-brand-maroon focus-within:ring-2 focus-within:ring-brand-maroon/20 transition shadow-2xs">
            <div id="keywords-chip-list" class="flex flex-wrap items-center gap-1.5"></div>
            <input 
                type="text" 
                id="keyword-input-box" 
                placeholder="Ketik kata kunci lalu tekan Enter atau koma..." 
                class="flex-1 min-w-50 border-0 p-1 text-xs sm:text-sm focus:ring-0 focus:outline-none"
            >
        </div>
        <p class="text-[11px] text-gray-400">
            Saran: Gunakan 3–5 istilah spesifik (contoh: <em>Kebijakan Publik</em>, <em>Inovasi Daerah</em>, <em>Kota Makassar</em>).
        </p>
    </div>

    <!-- Bidang Keilmuan / Fokus Ruang Lingkup -->
    <div class="space-y-1.5">
        <label for="subject_area" class="block text-xs sm:text-sm font-bold text-gray-900">
            Bidang Keilmuan / Fokus Riset <span class="text-gray-400 font-normal">(Opsional)</span>
        </label>
        <input 
            type="text" 
            name="subject_area" 
            id="subject_area" 
            value="{{ old('subject_area') }}"
            placeholder="Contoh: Tata Kelola Pemerintahan & Inovasi Layanan Publik"
            class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition"
        >
    </div>

    <!-- Daftar Pustaka Awal / Referensi -->
    <div class="space-y-1.5">
        <label for="references" class="block text-xs sm:text-sm font-bold text-gray-900">
            Daftar Pustaka Awal <span class="text-gray-400 font-normal">(Opsional)</span>
        </label>
        <textarea 
            name="references" 
            id="references" 
            rows="4" 
            placeholder="Tempelkan daftar rujukan utama (disarankan format APA 7th)..."
            class="w-full p-3 sm:p-3.5 text-xs sm:text-sm rounded-xl border border-gray-300 bg-white text-gray-900 placeholder-gray-400 shadow-2xs hover:border-gray-400 focus:outline-none focus:border-brand-maroon focus:ring-2 focus:ring-brand-maroon/20 transition leading-relaxed"
        >{{ old('references') }}</textarea>
    </div>
</div>
