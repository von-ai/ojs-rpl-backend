<?php

declare(strict_types=1);

namespace App\ViewModels;

class JournalManagerSettingsViewModel
{
    /**
     * Profil pengguna aktif Journal Manager.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        return [
            'name' => 'Andi Muhammad Yusuf, M.Si.',
            'role_label' => 'Journal Manager',
            'affiliation' => 'BRIDA Kota Makassar',
            'initials' => 'AY',
        ];
    }

    /**
     * Konfigurasi metadata identitas resmi jurnal ilmiah.
     *
     * @return array{
     *     name: string,
     *     initials: string,
     *     slug: string,
     *     issn_print: string,
     *     issn_online: string,
     *     publisher: string,
     *     editorial_email: string,
     *     contact_phone: string,
     *     status: string,
     *     status_label: string
     * }
     */
    public function journalIdentity(): array
    {
        return [
            'name' => 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)',
            'initials' => 'SIRIDA',
            'slug' => 'sirida',
            'issn_print' => '2746-8194',
            'issn_online' => '2746-8208',
            'publisher' => 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar',
            'editorial_email' => 'jurnal.brida@makassar.go.id',
            'contact_phone' => '+62 411 873-456',
            'status' => 'active',
            'status_label' => 'Aktif & Terdaftar',
        ];
    }

    /**
     * Kebijakan editorial dan standar penerbitan jurnal.
     *
     * @return array{
     *     focus_scope: string,
     *     frequency: string,
     *     open_access_policy: string,
     *     apc_fee: string,
     *     peer_review_policy: string,
     *     license: string
     * }
     */
    public function editorialPolicies(): array
    {
        return [
            'focus_scope' => 'Jurnal mempublikasikan naskah ilmiah hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, ketahanan lingkungan pesisir, dan pembangunan daerah terpadu.',
            'frequency' => '2 Kali Setahun (Nomor 1 terbit pada bulan Juni, Nomor 2 terbit pada bulan Desember)',
            'open_access_policy' => 'Jurnal ini menyediakan akses terbuka langsung (Gold Open Access) ke seluruh isi naskah dengan prinsip bahwa membuat hasil penelitian dapat diakses secara bebas oleh publik demi mendukung pertukaran pengetahuan global.',
            'apc_fee' => 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah - Didanai Penuh oleh APBD BRIDA Kota Makassar)',
            'peer_review_policy' => 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan selama proses penelaahan).',
            'license' => 'Creative Commons Attribution 4.0 International (CC BY 4.0)',
        ];
    }

    /**
     * Daftar warta pengumuman publik (announcements) jurnal.
     *
     * @return array<int, array{
     *     id: string,
     *     title: string,
     *     body: string,
     *     published_at: string,
     *     is_published: bool
     * }>
     */
    public function announcements(): array
    {
        return [
            [
                'id' => 'ANN-01JARK-01',
                'title' => 'Panggilan Pengajuan Naskah (Call for Papers) Vol. 6 No. 2 Tahun 2026',
                'body' => 'Redaksi mengundang para akademisi, peneliti, dan praktisi kebijakan daerah untuk mengirimkan naskah hasil riset terbaik bertema Inovasi Kebijakan Publik dan Ketahanan Perkotaan. Batas akhir pengiriman naskah adalah 15 November 2026.',
                'published_at' => '01 Okt 2026',
                'is_published' => true,
            ],
            [
                'id' => 'ANN-01JARK-02',
                'title' => 'Pembaruan Panduan Gaya Selingkung Mengacu pada EYD Edisi V dan Format Sitasi APA 7th Edition',
                'body' => 'Mulai terbitan Volume 6 Tahun 2026, seluruh naskah yang diajukan wajib mengikuti format acuan bibliografi APA Style 7th Edition dan tata bahasa EYD Edisi V.',
                'published_at' => '15 Agu 2026',
                'is_published' => true,
            ],
        ];
    }
}
