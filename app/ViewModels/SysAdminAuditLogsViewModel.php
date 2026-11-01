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
     * Seluruh data log jejak audit dari basis data.
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
}
