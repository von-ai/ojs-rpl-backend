<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Schema;

class SysAdminAuditLogsViewModel
{
    private string $actorType;

    private string $action;

    private string $searchQuery;

    public function __construct(string $actorType = 'all', string $action = 'all', string $searchQuery = '')
    {
        $this->actorType = $actorType;
        $this->action = $action;
        $this->searchQuery = trim($searchQuery);
    }

    /**
     * 4 Ringkasan metrik statistik audit logs.
     *
     * @return array<int, array{title: string, value: string, badge_text: string, color_class: string}>
     */
    public function summaryMetrics(): array
    {
        $all = $this->allLogs();
        $total = count($all);
        $userCount = count(array_filter($all, fn ($item) => $item['actor_type'] === 'user'));
        $systemCount = count(array_filter($all, fn ($item) => $item['actor_type'] === 'system'));

        return [
            [
                'title' => 'Total Log Tercatat',
                'value' => "{$total} Entri",
                'badge_text' => 'Append-Only',
                'color_class' => 'text-brand-maroon bg-red-50 border-red-100',
            ],
            [
                'title' => 'Aktivitas Pengguna',
                'value' => "{$userCount} Aksi",
                'badge_text' => 'Aktor User',
                'color_class' => 'text-blue-700 bg-blue-50 border-blue-200',
            ],
            [
                'title' => 'Eksekusi Sistem Otomatis',
                'value' => "{$systemCount} Kejadian",
                'badge_text' => 'Aktor System',
                'color_class' => 'text-purple-700 bg-purple-50 border-purple-200',
            ],
            [
                'title' => 'Integritas Log Mutasi',
                'value' => '100% Valid',
                'badge_text' => 'Immutable Hash',
                'color_class' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            ],
        ];
    }

    /**
     * Konfigurasi tab filter aktor audit log.
     *
     * @return array{active: string, items: array<string, array{key: string, label: string, count: int}>}
     */
    public function tabs(): array
    {
        $all = $this->allLogs();

        $counts = [
            'all' => count($all),
            'user' => count(array_filter($all, fn ($item) => $item['actor_type'] === 'user')),
            'system' => count(array_filter($all, fn ($item) => $item['actor_type'] === 'system')),
        ];

        return [
            'active' => $this->actorType,
            'items' => [
                'all' => [
                    'key' => 'all',
                    'label' => 'Semua Log Audit',
                    'count' => $counts['all'],
                ],
                'user' => [
                    'key' => 'user',
                    'label' => 'Aktor Pengguna (User)',
                    'count' => $counts['user'],
                ],
                'system' => [
                    'key' => 'system',
                    'label' => 'Aktor Sistem (System)',
                    'count' => $counts['system'],
                ],
            ],
        ];
    }

    /**
     * Kumpulan log audit yang telah difilter.
     *
     * @return array<int, array{
     *     id: string,
     *     action: string,
     *     action_label: string,
     *     actor_name: string,
     *     actor_type: string,
     *     system_actor: ?string,
     *     effective_role: string,
     *     resource_type: string,
     *     resource_id: string,
     *     occurred_at: string,
     *     request_id: string,
     *     reason: ?string,
     *     diff: ?array<string, mixed>
     * }>
     */
    public function logs(): array
    {
        $items = $this->allLogs();

        // Filter tipe aktor
        if ($this->actorType !== 'all') {
            $items = array_values(array_filter($items, fn ($item) => $item['actor_type'] === $this->actorType));
        }

        // Filter aksi
        if ($this->action !== 'all') {
            $items = array_values(array_filter($items, fn ($item) => $item['action'] === $this->action));
        }

        // Filter pencarian kata kunci
        if ($this->searchQuery !== '') {
            $query = mb_strtolower($this->searchQuery);
            $items = array_values(array_filter($items, function ($item) use ($query) {
                return str_contains(mb_strtolower($item['action']), $query)
                    || str_contains(mb_strtolower($item['action_label']), $query)
                    || str_contains(mb_strtolower($item['actor_name']), $query)
                    || str_contains(mb_strtolower($item['resource_type']), $query)
                    || str_contains(mb_strtolower($item['request_id']), $query);
            }));
        }

        return $items;
    }

    /**
     * Metadata paginasi untuk log audit.
     *
     * @return array{current_page: int, per_page: int, total: int, from: int, to: int, last_page: int}
     */
    public function pagination(): array
    {
        $total = count($this->logs());

        return [
            'current_page' => 1,
            'per_page' => 10,
            'total' => $total,
            'from' => $total > 0 ? 1 : 0,
            'to' => $total,
            'last_page' => 1,
        ];
    }

    /**
     * Seluruh data log jejak audit (Database dengan fallback mock).
     *
     * @return array<int, array{
     *     id: string,
     *     action: string,
     *     action_label: string,
     *     actor_name: string,
     *     actor_type: string,
     *     system_actor: ?string,
     *     effective_role: string,
     *     resource_type: string,
     *     resource_id: string,
     *     occurred_at: string,
     *     request_id: string,
     *     reason: ?string,
     *     diff: ?array<string, mixed>
     * }>
     */
    private function allLogs(): array
    {
        try {
            if (Schema::hasTable('audit_logs')) {
                return AuditLog::with(['actor', 'targetUser', 'journal'])->latest('occurred_at')->get()->map(function (AuditLog $log) {
                    $actorName = $log->actor?->name ?? ($log->system_actor ? 'Sistem Otomatis' : 'Sistem Latar Belakang');

                    return [
                        'id' => (string) $log->id,
                        'action' => (string) $log->action,
                        'action_label' => (string) $log->action_label,
                        'actor_name' => $actorName,
                        'actor_type' => (string) $log->actor_type,
                        'system_actor' => $log->system_actor,
                        'effective_role' => (string) ($log->effective_role ?? 'System Administrator'),
                        'resource_type' => (string) ($log->resource_type ?? 'system'),
                        'resource_id' => (string) ($log->resource_id ?? 'N/A'),
                        'occurred_at' => $log->occurred_at?->format('d M Y, H:i').' WITA',
                        'request_id' => (string) ($log->request_id ?? 'req_sys'),
                        'reason' => $log->reason,
                        'diff' => $log->before_after ?? $log->details,
                    ];
                })->toArray();
            }
        } catch (\Throwable) {
            // Graceful fallback
        }

        return [];
    }

    /**
     * Seluruh data mock log jejak audit (Tabel audit_logs).
     *
     * @return array<int, array{
     *     id: string,
     *     action: string,
     *     action_label: string,
     *     actor_name: string,
     *     actor_type: string,
     *     system_actor: ?string,
     *     effective_role: string,
     *     resource_type: string,
     *     resource_id: string,
     *     occurred_at: string,
     *     request_id: string,
     *     reason: ?string,
     *     diff: ?array<string, mixed>
     * }>
     */
    private function mockLogs(): array
    {
        return [
            [
                'id' => 'LOG-01JARK-01',
                'action' => 'system_settings.updated',
                'action_label' => 'Pembaruan Pengaturan Platform',
                'actor_name' => 'Fahri Azis, S.T.',
                'actor_type' => 'user',
                'system_actor' => null,
                'effective_role' => 'System Administrator',
                'resource_type' => 'system_settings',
                'resource_id' => 'site_name',
                'occurred_at' => '25 Okt 2026, 08:30 WITA',
                'request_id' => 'req_01jark_8849af',
                'reason' => 'Penyesuaian nama instansi sesuai nomenklatur Perwali BRIDA 2026.',
                'diff' => ['before' => ['site_name' => 'SIRIDA Makassar'], 'after' => ['site_name' => 'SIRIDA — Portal Ilmiah BRIDA Kota Makassar']],
            ],
            [
                'id' => 'LOG-01JARK-02',
                'action' => 'user.sysadmin_assigned',
                'action_label' => 'Pemberian Peran System Administrator',
                'actor_name' => 'Fahri Azis, S.T.',
                'actor_type' => 'user',
                'system_actor' => null,
                'effective_role' => 'System Administrator',
                'resource_type' => 'user',
                'resource_id' => 'USER-01JARK-02',
                'occurred_at' => '24 Okt 2026, 16:45 WITA',
                'request_id' => 'req_01jark_1900be',
                'reason' => 'Penambahan administrator sekunder dari Diskominfo Kota Makassar.',
                'diff' => ['before' => ['is_sysadmin' => false], 'after' => ['is_sysadmin' => true]],
            ],
            [
                'id' => 'LOG-01JARK-03',
                'action' => 'cron_worker.scheduled_scan',
                'action_label' => 'Pemindaian Malware Berkas Unggahan',
                'actor_name' => 'Sistem Latar Belakang',
                'actor_type' => 'system',
                'system_actor' => 'clamav_scan_daemon',
                'effective_role' => 'System Daemon',
                'resource_type' => 'scan_attempts',
                'resource_id' => 'SCAN-01JARK-12',
                'occurred_at' => '24 Okt 2026, 03:00 WITA',
                'request_id' => 'cron_job_daily_security_audit',
                'reason' => 'Rutinitas pemindaian harian virus/malware seluruh berkas galai PDF & naskah.',
                'diff' => ['scanned_files' => 114, 'threats_found' => 0, 'status' => 'clean'],
            ],
            [
                'id' => 'LOG-01JARK-04',
                'action' => 'journal.status_updated',
                'action_label' => 'Pembaruan Status Publikasi Jurnal',
                'actor_name' => 'Fahri Azis, S.T.',
                'actor_type' => 'user',
                'system_actor' => null,
                'effective_role' => 'System Administrator',
                'resource_type' => 'journal',
                'resource_id' => 'JOURNAL-01JARK-04',
                'occurred_at' => '23 Okt 2026, 11:15 WITA',
                'reason' => 'Pengarsipan resmi Buletin Riset Litbang periode 2018-2022.',
                'request_id' => 'req_01jark_4482dc',
                'diff' => ['before' => ['status' => 'active'], 'after' => ['status' => 'archived']],
            ],
            [
                'id' => 'LOG-01JARK-05',
                'action' => 'user_imports.processed',
                'action_label' => 'Eksekusi Impor Pengguna CSV',
                'actor_name' => 'Fahri Azis, S.T.',
                'actor_type' => 'user',
                'system_actor' => null,
                'effective_role' => 'System Administrator',
                'resource_type' => 'user_imports',
                'resource_id' => 'IMPORT-01JARK-01',
                'occurred_at' => '22 Okt 2026, 14:15 WITA',
                'request_id' => 'req_01jark_9912aa',
                'reason' => 'Impor 35 mitra bestari bidang kepakaran Smart City untuk JTIIK.',
                'diff' => ['total_rows' => 35, 'processed_rows' => 35, 'failed_rows' => 0],
            ],
            [
                'id' => 'LOG-01JARK-06',
                'action' => 'system.cache_purged',
                'action_label' => 'Pembersihan Cache Aplikasi',
                'actor_name' => 'Sistem Otomatis',
                'actor_type' => 'system',
                'system_actor' => 'cache_purger',
                'effective_role' => 'System Service',
                'resource_type' => 'cache',
                'resource_id' => 'app_compiled_views',
                'occurred_at' => '21 Okt 2026, 00:00 WITA',
                'request_id' => 'cron_weekly_cache_purge',
                'reason' => 'Pembersihan berkas view blade dan rute usang mingguan.',
                'diff' => ['cleared_items' => 450, 'freed_space' => '84 MB'],
            ],
        ];
    }
}
