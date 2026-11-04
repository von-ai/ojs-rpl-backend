<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Announcement;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class JournalManagerSettingsViewModel
{
    /**
     * Profil pengguna aktif Journal Manager.
     *
     * @return array{name: string, role_label: string, affiliation: string, initials: string}
     */
    public function userProfile(): array
    {
        $user = Auth::user();
        $name = $user?->name ?? 'Andi Muhammad Yusuf, M.Si.';
        $affiliation = $user?->profile?->affiliation ?? 'BRIDA Kota Makassar';

        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name) ?: 'AY';
        $initials = strtoupper(substr($cleanName, 0, 2));

        return [
            'name' => $name,
            'role_label' => 'Journal Manager',
            'affiliation' => $affiliation,
            'initials' => $initials,
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
        $journal = $this->resolveJournal();

        return [
            'name' => (string) ($journal?->name ?? 'Jurnal Riset dan Inovasi Daerah Kota Makassar (SIRIDA)'),
            'initials' => (string) ($journal?->initials ?? 'SIRIDA'),
            'slug' => (string) ($journal?->slug ?? 'sirida'),
            'issn_print' => (string) ($journal?->issn ?? '2746-8194'),
            'issn_online' => (string) ($journal?->eissn ?? '2746-8208'),
            'publisher' => (string) ($journal?->publisher ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Pemerintah Kota Makassar'),
            'editorial_email' => (string) ($journal?->editorial_email ?? 'jurnal.brida@makassar.go.id'),
            'contact_phone' => (string) ($journal?->contact_phone ?? '+62 411 873-456'),
            'status' => (string) ($journal?->status ?? 'active'),
            'status_label' => ($journal?->status === 'active') ? 'Aktif & Terdaftar' : 'Dalam Pemeliharaan',
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
     *     apc_enabled: bool,
     *     apc_amount: string,
     *     payment_bank_name: string,
     *     payment_account_number: string,
     *     payment_account_holder: string,
     *     payment_instructions: string,
     *     is_paid: bool,
     *     peer_review_policy: string,
     *     license: string
     * }
     */
    public function editorialPolicies(): array
    {
        $journal = $this->resolveJournal();

        return [
            'focus_scope' => (string) ($journal?->focus_scope ?? 'Jurnal mempublikasikan naskah ilmiah hasil riset empiris dan konseptual di bidang inovasi kebijakan publik, tata kelola pemerintahan perkotaan, ketahanan lingkungan pesisir, dan pembangunan daerah terpadu.'),
            'frequency' => (string) ($journal?->frequency ?? '2 Kali Setahun (Nomor 1 terbit pada bulan Juni, Nomor 2 terbit pada bulan Desember)'),
            'open_access_policy' => (string) ($journal?->open_access_policy ?? 'Jurnal ini menyediakan akses terbuka langsung (Gold Open Access) ke seluruh isi naskah dengan prinsip bahwa membuat hasil penelitian dapat diakses secara bebas oleh publik demi mendukung pertukaran pengetahuan global.'),
            'apc_fee' => (string) ($journal?->apc_fee ?? 'Rp 0,- (Bebas Biaya Pemrosesan & Publikasi Naskah - Didanai Penuh oleh APBD BRIDA Kota Makassar)'),
            'apc_enabled' => (bool) ($journal?->isPaidApc() ?? false),
            'apc_amount' => (string) ($journal?->apc_amount ? number_format((float) $journal->apc_amount, 0, ',', '.') : '500.000'),
            'payment_bank_name' => (string) ($journal?->payment_bank_name ?? 'Bank Sulselbar'),
            'payment_account_number' => (string) ($journal?->payment_account_number ?? '130-001-009876-5'),
            'payment_account_holder' => (string) ($journal?->payment_account_holder ?? 'Badan Riset dan Inovasi Daerah (BRIDA) Kota Makassar'),
            'payment_instructions' => (string) ($journal?->payment_instructions ?? 'Sertakan ID Naskah pada berita transfer (contoh: SUB-2026-00106). Setelah melakukan transfer, unggah foto/berkas bukti transfer pembayaran melalui portal penulis.'),
            'is_paid' => (bool) ($journal?->isPaidApc() ?? false),
            'peer_review_policy' => (string) ($journal?->peer_review_policy ?? 'Double-Blind Peer Review (Identitas penulis dan mitra bestari saling dirahasiakan selama proses penelaahan).'),
            'license' => (string) ($journal?->license ?? 'Creative Commons Attribution 4.0 International (CC BY 4.0)'),
        ];
    }

    /**
     * Daftar warta pengumuman publik (announcements) jurnal dari basis data.
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
        $journal = $this->resolveJournal();

        if ($journal && Schema::hasTable('announcements')) {
            $announcements = Announcement::where('journal_id', $journal->id)
                ->orderByDesc('published_at')
                ->get();

            if ($announcements->isNotEmpty()) {
                return $announcements->map(function (Announcement $ann) {
                    return [
                        'id' => (string) $ann->id,
                        'title' => (string) $ann->title,
                        'body' => (string) $ann->body,
                        'published_at' => $ann->published_at ? $ann->published_at->translatedFormat('d M Y') : 'Baru saja',
                        'is_published' => (bool) $ann->is_published,
                    ];
                })->all();
            }
        }

        return [];
    }

    private function resolveJournal(): ?Journal
    {
        if (Schema::hasTable('journals')) {
            return Journal::first();
        }

        return null;
    }
}
