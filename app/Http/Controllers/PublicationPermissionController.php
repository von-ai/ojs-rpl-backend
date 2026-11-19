<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicationPermissionController extends Controller
{
    public function __invoke(Request $request, string $id): RedirectResponse
    {
        $editor = $request->routeIs('eic.*');
        $data = $request->validate([
            'confirmed' => ['required', 'accepted'],
            'license' => ['required', 'string', 'max:191'],
            'agreement_reference' => $editor ? ['required', 'string', 'max:255'] : ['prohibited'],
        ]);

        DB::transaction(function () use ($request, $id, $editor, $data) {
            $submission = Submission::query()->lockForUpdate()->findOrFail($id);
            $user = $request->user();
            abort_if($request->session()->has('active_journal_id') && $request->session()->get('active_journal_id') !== $submission->journal_id, 404);
            if ($editor) {
                abort_unless($user->userRoles()->where('journal_id', $submission->journal_id)->where('role_code', 'editor_in_chief')->exists(), 403);
            } else {
                abort_unless($user->id === $submission->author_id, 403);
            }
            abort_if($submission->status === 'published', 409);
            $license = trim((string) $submission->journal()->lockForUpdate()->value('license'));
            if ($license === '' || $license !== $data['license']) {
                throw ValidationException::withMessages(['license' => 'Lisensi berubah. Muat ulang halaman dan periksa lisensi jurnal.']);
            }
            $evidence = [
                'submission_id' => $submission->id, 'actor_id' => $user->id,
                'license' => $license, 'source' => $editor ? 'written_agreement' : 'author',
                'agreement_reference' => $data['agreement_reference'] ?? null,
            ];
            $latest = DB::table('publication_permissions')->where('submission_id', $id)->latest('id')->first();
            if (! $latest || $latest->actor_id !== $user->id || $latest->license !== $license || $latest->agreement_reference !== $evidence['agreement_reference'] || $latest->source !== $evidence['source']) {
                DB::table('publication_permissions')->insert($evidence + ['recorded_at' => now()]);
            }
        });

        return back()->with('success', 'Izin penerbitan tercatat.');
    }
}
