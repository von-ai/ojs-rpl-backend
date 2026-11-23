<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Journal;
use Illuminate\Database\Seeder;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Journal::firstOrCreate(
            ['slug' => 'sirida-makassar'],
            [
                'name' => 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)',
                'initials' => 'SIRIDA',
                'issn' => '2746-8194',
                'eissn' => '2746-8208',
                'publisher' => 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
                'editorial_email' => 'jurnal.brida@makassar.go.id',
                'contact_phone' => '+62 411 873-456',
                'focus_scope' => 'Jurnal mempublikasikan naskah ilmiah hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, ketahanan lingkungan pesisir, dan pembangunan daerah terpadu.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        Journal::firstOrCreate(
            ['slug' => 'jtiik-makassar'],
            [
                'name' => 'Jurnal Teknologi Informasi dan Inovasi Perkotaan (JTIIK)',
                'initials' => 'JTIIK',
                'issn' => '2655-9012',
                'eissn' => '2722-4567',
                'publisher' => 'Pusat Studi Informatika & Smart City BRIDA Makassar',
                'editorial_email' => 'jtiik@makassar.go.id',
                'contact_phone' => '+62 411 855-123',
                'focus_scope' => 'Publikasi ilmiah berkala memuat riset terapan smart city, IoT, dan transformasi digital pemerintahan Kota Makassar.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        Journal::firstOrCreate(
            ['slug' => 'sirida-inovasi'],
            [
                'name' => 'Jurnal Inovasi Daerah & Kebijakan Publik Makassar (JIKPM)',
                'initials' => 'JIKPM',
                'issn' => '2774-8890',
                'eissn' => '2809-1234',
                'publisher' => 'Pusat Analisis Kebijakan & Inovasi Daerah',
                'editorial_email' => 'jikpm@makassar.go.id',
                'contact_phone' => '+62 411 855-456',
                'focus_scope' => 'Kajian empiris efektivitas tata kelola birokrasi, inovasi pelayanan publik, dan pembiayaan pembangunan daerah.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );

        Journal::firstOrCreate(
            ['slug' => 'jurnal-lingkungan'],
            [
                'name' => 'Jurnal Riset Lingkungan & Mitigasi Bencana Pesisir (JRLMBP)',
                'initials' => 'JRLMBP',
                'issn' => '2985-3341',
                'eissn' => '2986-7789',
                'publisher' => 'Pusat Riset Mitigasi Bencana & Oseanografi BRIDA',
                'editorial_email' => 'lingkungan@makassar.go.id',
                'contact_phone' => '+62 411 855-789',
                'focus_scope' => 'Kajian multidisiplin oseanografi, mitigasi banjir rob kawasan kepulauan spermonde, dan adaptasi perubahan iklim.',
                'frequency' => '2 Kali Setahun',
                'status' => 'active',
                'version' => 1,
            ]
        );
    }
}
