<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\EditorialMediationService;
use DomainException;
use Illuminate\Http\Request;

class EditorialMediationController extends Controller
{
    public function resolve(Request $request, string $id, string $escalationId)
    {
        $data = $request->validate(['resolution' => ['required', 'string', 'min:5', 'max:4000']]);
        $submission = Submission::findOrFail($id);
        $escalation = $submission->escalations()->findOrFail($escalationId);
        try {
            app(EditorialMediationService::class)->resolve($escalation, $request->user(), $request->attributes->get('portal.role'), $data['resolution']);
        } catch (DomainException $e) {
            return back()->withErrors(['resolution' => $e->getMessage()]);
        }

        return back()->with('success', 'Penyelesaian mediasi tersimpan. Tim produksi dapat melanjutkan pekerjaan.');
    }
}
