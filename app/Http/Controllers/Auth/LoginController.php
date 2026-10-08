<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Menampilkan halaman login Portal Penulis BRIDA Makassar.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses permintaan autentikasi login pengguna dengan Form Request & Rate Limiting.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Selamat datang kembali di Portal Penulis BRIDA Makassar!');
    }

    /**
     * Memproses logout pengguna dari aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Anda telah berhasil keluar dari sesi Portal Penulis.');
    }
}
