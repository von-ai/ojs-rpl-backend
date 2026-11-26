<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JournalSwitchController extends Controller
{
    /**
     * Mengalihkan konteks jurnal aktif pengguna yang sedang login.
     * Standar OJS: Berlaku lintas seluruh peran (Manager, EIC, Section Editor, Reviewer, Author, dll).
     */
    public function switchJournal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'journal_id' => ['required', 'string', 'exists:journals,id'],
            'current_role' => ['nullable', 'string'],
        ]);

        $journalId = (string) $validated['journal_id'];
        $journal = Journal::withoutGlobalScope('portal_access')->findOrFail($journalId);
        $user = $request->user();

        if (! $user) {
            abort(401, 'Unauthenticated.');
        }

        // 1. Simpan ID jurnal aktif ke dalam session
        $request->session()->put('active_journal_id', $journal->id);

        // 2. Tentukan peran aktif saat ini
        $currentRole = $validated['current_role']
            ?? $request->session()->get('active_role')
            ?? $user->activeRoleCode();

        // 3. Periksa apakah peran saat ini sah di jurnal tujuan (Standar OJS)
        $userRolesInTarget = $user->userRoles()
            ->where('journal_id', $journal->id)
            ->pluck('role_code')
            ->all();

        $targetRole = $currentRole;

        if (! $user->isSysAdmin() && $currentRole !== 'author') {
            if (! in_array($currentRole, $userRolesInTarget, true)) {
                // Jika pengguna tidak memiliki peran yang sama di jurnal tujuan,
                // alihkan ke peran tertinggi yang dimiliki pengguna pada jurnal tujuan
                $catalogKeys = array_keys(UserRole::roleCatalog());
                $nextRole = collect($catalogKeys)->first(fn ($code) => in_array($code, $userRolesInTarget, true)) ?? 'author';
                $request->session()->put('active_role', $nextRole);
                $targetRole = $nextRole;
            }
        }

        $meta = UserRole::roleMeta($targetRole);
        $message = "Konteks jurnal aktif berhasil dialihkan ke {$journal->name}.";

        return redirect()
            ->route($meta['route'])
            ->with('journal_switched_toast', $message);
    }
}
