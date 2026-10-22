<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Journal;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $journal = Journal::first() ?? Journal::create([
            'slug' => 'sirida-makassar',
            'name' => 'Jurnal Penelitian dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'issn' => '2985-7821',
            'eissn' => '2985-7821',
            'status' => 'active',
            'version' => 1,
        ]);

        $sections = [
            [
                'name' => 'Artikel Riset Orisinil',
                'slug' => 'sec-riset-orisinil',
                'description' => 'Laporan hasil penelitian empiris atau teoritis primer yang orisinil dan relevan dengan pembangunan daerah Kota Makassar.',
                'policy' => 'Wajib memuat metodologi riset yang jelas, data empiris primer/sekunder terverifikasi, dan analisis kebaruan.',
                'position' => 1,
            ],
            [
                'name' => 'Kajian Kebijakan Strategis',
                'slug' => 'sec-kajian-kebijakan',
                'description' => 'Telaah kritis berbasis bukti ilmiah untuk rekomendasi kebijakan publik dan inovasi daerah.',
                'policy' => 'Fokus pada rekomendasi actionable bagi Pemerintah Kota Makassar dan pemangku kepentingan.',
                'position' => 2,
            ],
            [
                'name' => 'Studi Kasus & Inovasi Daerah',
                'slug' => 'sec-studi-kasus',
                'description' => 'Dokumentasi implementasi inovasi tata kelola pemerintahan, layanan publik, atau teknologi tepat guna.',
                'policy' => 'Menguraikan tantangan nyata, proses implementasi solusi, dan dampak terukur terhadap masyarakat.',
                'position' => 3,
            ],
            [
                'name' => 'Tinjauan Pustaka Sistematis',
                'slug' => 'sec-tinjauan-literatur',
                'description' => 'Sintesis komprehensif dan meta-analisis literatur ilmiah terkini mengenai topik strategis perkotaan dan sains terapan.',
                'policy' => 'Wajib menggunakan protokol telaah terstandar seperti PRISMA dan mencantumkan kriteria inklusi serta eksklusi rujukan yang jelas.',
                'position' => 4,
            ],
            [
                'name' => 'Komunikasi Singkat Riset',
                'slug' => 'sec-komunikasi-pendek',
                'description' => 'Laporan ringkas hasil riset penting tahap awal atau temuan eksperimen mendesak yang membutuhkan publikasi cepat.',
                'policy' => 'Memprioritaskan temuan baru berurgensi tinggi dengan visualisasi data yang padat dan informatif.',
                'position' => 5,
            ],
            [
                'name' => 'Opini Kebijakan & Komentar Ilmiah',
                'slug' => 'sec-opini-kebijakan',
                'description' => 'Perspektif analitis dari pakar atau akademisi terhadap fenomena sosial-ekonomi atau regulasi daerah Kota Makassar.',
                'policy' => 'Gagasan harus berargumen rasional, didukung data faktual, dan menyajikan solusi konstruktif bagi pemangku kebijakan.',
                'position' => 6,
            ],
        ];

        foreach ($sections as $s) {
            Section::firstOrCreate(
                [
                    'journal_id' => $journal->id,
                    'slug' => $s['slug'],
                ],
                [
                    'name' => $s['name'],
                    'description' => $s['description'],
                    'policy' => $s['policy'],
                    'accepting_submissions' => true,
                    'position' => $s['position'],
                    'version' => 1,
                ]
            );
        }
    }
}
