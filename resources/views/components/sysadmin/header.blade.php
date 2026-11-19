@props([
    'title' => null,
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Fahri Azis, S.T.',
        'affiliation' => 'Pengelola Sistem & Infrastruktur IT BRIDA',
        'initials' => 'FA',
    ],
])

@php
    $computedPageTitle = ($title && $title !== 'Tata Kelola Platform SIRIDA') ? $title : null;
    if (!$computedPageTitle) {
        if (request()->routeIs('sysadmin.journals.*') || request()->routeIs('sysadmin.dashboard')) {
            $computedPageTitle = 'Instansi Jurnal';
        } elseif (request()->routeIs('sysadmin.users.*')) {
            $computedPageTitle = 'Pengguna & Akses';
        } elseif (request()->routeIs('sysadmin.settings.*')) {
            $computedPageTitle = 'Pengaturan Situs';
        } elseif (request()->routeIs('sysadmin.audit-logs.*')) {
            $computedPageTitle = 'Jejak Audit';
        }
    }
@endphp

<x-portal.header
    role-code="sysadmin"
    portal-title="System Admin"
    :home-route="route('sysadmin.journals.index')"
    :page-title="$computedPageTitle"
    :default-name="$user['name'] ?? 'Fahri Azis, S.T.'"
    :default-sub="$user['affiliation'] ?? 'Pengelola Sistem IT BRIDA'"
    :default-initials="$user['initials'] ?? 'FA'"
/>
