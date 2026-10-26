<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Author\AuthorSubmissionController;
use App\Http\Controllers\Copyeditor\CopyeditorSubmissionController;
use App\Http\Controllers\JournalManager\JournalManagerController;
use App\Http\Controllers\LayoutEditor\LayoutEditorSubmissionController;
use App\Http\Controllers\Proofreader\ProofreaderSubmissionController;
use App\Http\Controllers\SysAdmin\SysAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Autentikasi Pengguna (Login & Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('throttle:identity')->group(function () {
    Route::get('/register', [RegistrationController::class, 'form'])->name('register');
    Route::post('/register', [RegistrationController::class, 'register'])->name('register.store');
    Route::get('/register/check-email', [RegistrationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify', [RegistrationController::class, 'verificationForm'])->name('verification.form');
    Route::post('/email/verify', [RegistrationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [RegistrationController::class, 'resend'])->name('verification.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Portal Penulis (Author Submissions & Management) - Wajib Autentikasi
Route::middleware('auth')->prefix('author')->name('author.')->group(function () {
    Route::get('/submissions', [AuthorSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/create', [AuthorSubmissionController::class, 'create'])->name('submissions.create');
    Route::get('/submissions/sections', [AuthorSubmissionController::class, 'sectionsApi'])->name('submissions.sections');
    Route::post('/submissions', [AuthorSubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/export-csv', [AuthorSubmissionController::class, 'exportCsv'])->name('submissions.export');
    Route::get('/submissions/{id}', [AuthorSubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{id}/withdraw', [AuthorSubmissionController::class, 'withdraw'])->name('submissions.withdraw');
});

// Portal Copy Editor (Ruang Kerja Produksi & Dialog Transaksional)
Route::prefix('copyeditor')->name('copyeditor.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('copyeditor.submissions.index');
    })->name('dashboard');
    Route::get('/submissions', [CopyeditorSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{id}', [CopyeditorSubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/guidelines', [CopyeditorSubmissionController::class, 'guidelines'])->name('guidelines');
    Route::get('/catalog', [CopyeditorSubmissionController::class, 'catalog'])->name('catalog');
    Route::post('/upload', [CopyeditorSubmissionController::class, 'uploadFile'])->name('upload');
    Route::post('/request-approval', [CopyeditorSubmissionController::class, 'requestApproval'])->name('request-approval');
    Route::post('/escalate', [CopyeditorSubmissionController::class, 'submitEscalation'])->name('escalate');
    Route::post('/complete-handoff', [CopyeditorSubmissionController::class, 'completeHandoff'])->name('complete-handoff');
    Route::post('/author-decision', [CopyeditorSubmissionController::class, 'authorDecision'])->name('author-decision');
    Route::post('/resolve-escalation', [CopyeditorSubmissionController::class, 'resolveEscalation'])->name('resolve-escalation');
});

// Portal Layout Editor (Produksi Galai Publikasi & Proofreading)
Route::prefix('layouteditor')->name('layouteditor.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('layouteditor.submissions.index');
    })->name('dashboard');
    Route::get('/submissions', [LayoutEditorSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{id}', [LayoutEditorSubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/guidelines', [LayoutEditorSubmissionController::class, 'guidelines'])->name('guidelines');
    Route::post('/upload-galley', [LayoutEditorSubmissionController::class, 'uploadGalley'])->name('upload-galley');
    Route::post('/resolve-note', [LayoutEditorSubmissionController::class, 'resolveNote'])->name('resolve-note');
    Route::post('/request-proofread', [LayoutEditorSubmissionController::class, 'requestProofread'])->name('request-proofread');
    Route::post('/escalate', [LayoutEditorSubmissionController::class, 'submitEscalation'])->name('escalate');
    Route::post('/request-final-approval', [LayoutEditorSubmissionController::class, 'requestFinalApproval'])->name('request-final-approval');
});

// Portal Journal Manager (Tata Kelola Edisi, Tim Redaksi & Kebijakan)
Route::prefix('journalmanager')->name('journalmanager.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('journalmanager.issues.index');
    })->name('dashboard');

    // 1. Edisi & Terbitan (Issues)
    Route::get('/issues', [JournalManagerController::class, 'issues'])->name('issues.index');
    Route::post('/issues', [JournalManagerController::class, 'storeIssue'])->name('issues.store');
    Route::get('/issues/{id}', [JournalManagerController::class, 'showIssue'])->name('issues.show');
    Route::post('/issues/{id}/add-article', [JournalManagerController::class, 'addArticleToIssue'])->name('issues.add-article');
    Route::post('/issues/{id}/publish', [JournalManagerController::class, 'publishIssue'])->name('issues.publish');
    Route::post('/issues/{id}/set-current', [JournalManagerController::class, 'setCurrentIssue'])->name('issues.set-current');

    // 2. Pengguna, Tim Redaksi & Direktori Reviewer
    Route::get('/users', [JournalManagerController::class, 'users'])->name('users.index');
    Route::post('/users/assign-role', [JournalManagerController::class, 'assignRole'])->name('users.assign-role');
    Route::post('/users/reset-password', [JournalManagerController::class, 'resetPassword'])->name('users.reset-password');

    // 3. Bidang Naskah (Sections)
    Route::get('/sections', [JournalManagerController::class, 'sections'])->name('sections.index');
    Route::post('/sections', [JournalManagerController::class, 'storeSection'])->name('sections.store');

    // 4. Pengaturan Jurnal & Kebijakan
    Route::get('/settings', [JournalManagerController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [JournalManagerController::class, 'updateSettings'])->name('settings.update');
});

// Portal Proofreader (Verifikasi Bukti Baca Galai & Catatan Koreksi)
Route::prefix('proofreader')->name('proofreader.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('proofreader.submissions.index');
    })->name('dashboard');

    Route::get('/submissions', [ProofreaderSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{id}', [ProofreaderSubmissionController::class, 'show'])->name('submissions.show');
    Route::get('/guidelines', [ProofreaderSubmissionController::class, 'guidelines'])->name('guidelines');

    Route::post('/add-note', [ProofreaderSubmissionController::class, 'addNote'])->name('add-note');
    Route::post('/verify-note', [ProofreaderSubmissionController::class, 'verifyNote'])->name('verify-note');
    Route::post('/complete', [ProofreaderSubmissionController::class, 'completeProofread'])->name('complete');
    Route::post('/escalate', [ProofreaderSubmissionController::class, 'submitEscalation'])->name('escalate');
});

// Portal System Administrator (Tata Kelola Multi-Jurnal, Pengguna Global & Platform)
Route::prefix('sysadmin')->name('sysadmin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('sysadmin.journals.index');
    })->name('dashboard');

    // 1. Manajemen Jurnal Ilmiah (Multi-Tenancy)
    Route::get('/journals', [SysAdminController::class, 'journals'])->name('journals.index');
    Route::post('/journals', [SysAdminController::class, 'storeJournal'])->name('journals.store');
    Route::post('/journals/{id}/status', [SysAdminController::class, 'updateJournalStatus'])->name('journals.update-status');

    // 2. Direktori Pengguna & Hak Akses Global
    Route::get('/users', [SysAdminController::class, 'users'])->name('users.index');
    Route::post('/users/assign-sysadmin', [SysAdminController::class, 'assignSysAdminRole'])->name('users.assign-sysadmin');
    Route::post('/users/revoke-sysadmin', [SysAdminController::class, 'revokeSysAdminRole'])->name('users.revoke-sysadmin');
    Route::post('/users/import', [SysAdminController::class, 'importUsers'])->name('users.import');

    // 3. Pengaturan Platform & Kesehatan Server
    Route::get('/settings', [SysAdminController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [SysAdminController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/clear-cache', [SysAdminController::class, 'clearCache'])->name('settings.clear-cache');

    // 4. Jejak Audit & Pemantauan Sistem
    Route::get('/audit-logs', [SysAdminController::class, 'auditLogs'])->name('audit-logs.index');
});

Route::redirect('/submissions', '/author/submissions');
Route::get('/dashboard', function () {
    return redirect()->route('author.submissions.index');
})->middleware('auth')->name('dashboard');
