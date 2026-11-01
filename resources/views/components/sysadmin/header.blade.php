@props([
    'title' => 'Tata Kelola Platform SIRIDA',
    'breadcrumbs' => null,
    'user' => [
        'name' => 'Fahri Azis, S.T.',
        'affiliation' => 'Pengelola Sistem & Infrastruktur IT BRIDA',
        'initials' => 'FA',
    ],
])

<x-portal.header
    role-code="sysadmin"
    portal-title="System Admin"
    :home-route="route('sysadmin.journals.index')"
    :page-title="($title && $title !== 'Tata Kelola Platform SIRIDA') ? $title : null"
    :default-name="$user['name'] ?? 'Fahri Azis, S.T.'"
    :default-sub="$user['affiliation'] ?? 'Pengelola Sistem IT BRIDA'"
    :default-initials="$user['initials'] ?? 'FA'"
/>
