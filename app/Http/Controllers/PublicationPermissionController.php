<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicationPermissionRequest;
use App\Services\PublicationPermissionService;
use Illuminate\Http\RedirectResponse;

class PublicationPermissionController extends Controller
{
    public function __invoke(PublicationPermissionRequest $request, string $id, PublicationPermissionService $service): RedirectResponse
    {
        $service->record($request->user(), $request->session()->get('active_journal_id'), $id, $request->routeIs('eic.*'), $request->validated());

        return back()->with('success', 'Izin penerbitan tercatat.');
    }
}
