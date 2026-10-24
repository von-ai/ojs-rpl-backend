<div 
    id="style-guide-modal" 
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-200"
    role="dialog" 
    aria-modal="true" 
    aria-labelledby="style-guide-title"
>
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Header Modal -->
        <div class="px-6 py-4.5 border-b border-gray-200 flex items-center justify-between bg-surface-subtle/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-brand-maroon/10 text-brand-maroon flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <h2 id="style-guide-title" class="text-base font-bold text-gray-900">
                        Panduan Gaya Selingkung Jurnal
                    </h2>
                    <p class="text-xs text-gray-500">
                        Pedoman tata bahasa, format naskah, dan sitasi BRIDA Kota Makassar
                    </p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="closeStyleGuideModal()"
                class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition"
                aria-label="Tutup modal"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body Isi Panduan -->
        <div class="p-6 overflow-y-auto space-y-5 text-sm text-gray-700 leading-relaxed">
            <!-- Poin 1: Tata Bahasa & EYD -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-maroon flex items-center gap-1.5 mb-2">
                    <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                    1. Ejaan & Tata Bahasa (EYD V)
                </h3>
                <ul class="text-xs space-y-1.5 list-disc list-inside text-gray-600 pl-1">
                    <li>Gunakan bahasa baku sesuai Pedoman Umum Ejaan Bahasa Indonesia (EYD Edisi V).</li>
                    <li>Istilah asing atau daerah wajib dicetak miring (<em>italic</em>).</li>
                    <li>Singkatan atau akronim wajib ditulis lengkap pada kemunculan pertama.</li>
                </ul>
            </div>

            <!-- Poin 2: Tipografi & Format Halaman -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-maroon flex items-center gap-1.5 mb-2">
                    <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                    2. Tipografi & Tata Letak Naskah
                </h3>
                <div class="grid grid-cols-2 gap-3 text-xs text-gray-600">
                    <div>
                        <span class="font-semibold text-gray-800">Huruf Utama:</span> Times New Roman / Georgia 11pt
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800">Spasi Baris:</span> 1.15 spasi, After 6pt
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800">Judul Naskah:</span> 14pt Bold, Center
                    </div>
                    <div>
                        <span class="font-semibold text-gray-800">Abstrak:</span> 10pt Italic (Maksimal 250 kata)
                    </div>
                </div>
            </div>

            <!-- Poin 3: Sitasi & Daftar Pustaka -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-maroon flex items-center gap-1.5 mb-2">
                    <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                    3. Format Sitasi & Daftar Pustaka
                </h3>
                <p class="text-xs text-gray-600 mb-2">
                    Jurnal BRIDA menggunakan standar sitasi <strong>APA Style 7th Edition</strong>. Wajib menyertakan tautan DOI jika tersedia.
                </p>
                <div class="bg-white p-2.5 rounded border border-gray-200 font-mono text-[11px] text-gray-700">
                    Bahar, N., & Hamka, H. (2025). Analisis Spasial Ruang Terbuka Hijau. <em>Jurnal Riset Daerah</em>, 12(2), 45-58. https://doi.org/10.1234/jrd.v12i2.106
                </div>
            </div>

            <!-- Poin 4: Tabel & Gambar -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/80">
                <h3 class="text-xs font-bold uppercase tracking-wider text-brand-maroon flex items-center gap-1.5 mb-2">
                    <span class="w-2 h-2 rounded-full bg-brand-maroon"></span>
                    4. Ketentuan Gambar & Tabel
                </h3>
                <ul class="text-xs space-y-1.5 list-disc list-inside text-gray-600 pl-1">
                    <li>Resolusi gambar/peta minimal 300 DPI (format .PNG atau .JPEG tajam).</li>
                    <li>Tabel hanya menggunakan garis horizontal (garis kepala dan garis penutup, tanpa garis vertikal).</li>
                    <li>Judul tabel diletakkan di atas tabel; keterangan gambar diletakkan di bawah gambar.</li>
                </ul>
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="px-6 py-3.5 border-t border-gray-200 bg-gray-50 flex items-center justify-end">
            <button 
                type="button" 
                onclick="closeStyleGuideModal()"
                class="px-4 py-2 bg-brand-maroon hover:bg-brand-maroon-hover text-white text-xs font-semibold rounded-lg shadow-2xs transition"
            >
                Saya Mengerti
            </button>
        </div>
    </div>
</div>

<script>
    function openStyleGuideModal() {
        const modal = document.getElementById('style-guide-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeStyleGuideModal() {
        const modal = document.getElementById('style-guide-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
</script>
