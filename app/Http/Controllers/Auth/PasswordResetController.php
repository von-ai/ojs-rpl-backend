<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:254']]);
        Password::sendResetLink(['email' => strtolower(trim($data['email']))]);

        return back()->with('status', 'Jika alamat email terdaftar, tautan pengaturan ulang kata sandi akan dikirimkan.');
    }

    public function resetForm(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email', '')]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:128'],
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'min:12', 'max:128', 'confirmed'],
        ]);
        $data['email'] = strtolower(trim($data['email']));
        $status = DB::transaction(function () use ($data) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->lockForUpdate()->first();

            return Password::reset($data, function (User $user, string $password) {
                $user = User::query()->lockForUpdate()->findOrFail($user->id);
                $user->password_hash = Hash::make($password);
                $user->setRememberToken(null);
                $user->remember_expires_at = null;
                DB::table('auth_sessions')->where('user_id', $user->id)->whereNull('revoked_at')->update(['revoked_at' => now(), 'version' => DB::raw('version + 1'), 'updated_at' => now()]);
                $user->version++;
                $user->save();
            });
        });
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Tautan tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.'])->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diperbarui. Silakan masuk kembali.');
    }
}
