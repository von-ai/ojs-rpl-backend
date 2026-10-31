<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleSwitchController extends Controller
{
    /**
     * Mengalihkan peran aktif pengguna yang sedang login ke portal peran yang dipilih.
     */
    public function switchRole(Request $request): RedirectResponse
    {
        $catalogKeys = array_keys(UserRole::roleCatalog());

        $validated = $request->validate([
            'role_code' => ['required', 'string', Rule::in($catalogKeys)],
        ]);

        $roleCode = (string) $validated['role_code'];
        $user = $request->user();

        if (! $user || ! $user->hasRole($roleCode)) {
            abort(403, 'Anda tidak memiliki hak akses untuk beralih ke peran tersebut.');
        }

        $request->session()->put('active_role', $roleCode);

        $meta = UserRole::roleMeta($roleCode);
        $message = "Peran aktif berhasil dialihkan ke {$meta['label']}.";

        return redirect()
            ->route($meta['route'])
            ->with('role_switched_toast', $message);
    }
}
