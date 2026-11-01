@props(['viewModel' => null])

@php
    $profile = $viewModel ? $viewModel->userProfile() : [
        'name' => 'Dr. Andi Muhammad Ilyas, M.Si.',
        'title' => 'Dr. Andi Muhammad Ilyas, M.Si.',
        'affiliation' => 'Universitas Hasanuddin',
        'unread_notifications' => 2,
    ];
    $user = auth()->user();
    $defaultName = $user?->name ?? ($profile['title'] ?? ($profile['name'] ?? 'Penulis'));
    $defaultSub = $user?->email ?? ($profile['affiliation'] ?? 'Penulis');
@endphp

<x-portal.header
    role-code="author"
    portal-title="Portal Penulis"
    :home-route="route('author.submissions.index')"
    :default-name="$defaultName"
    :default-sub="$defaultSub"
    default-initials="AU"
/>
