@extends('layouts.dashboard')

@section('title', 'Dashboard Portal Penulis | BRIDA Kota Makassar')

@section('content')
    <!-- 1. Hero Welcome Banner -->
    <x-dashboard.welcome-banner :userProfile="$viewModel->userProfile()" />

    <!-- 2. Submission Workflow Status Summary Cards -->
    <x-dashboard.status-summary :stats="$viewModel->submissionStats()" />

    <!-- 3. Urgent Action Required Tasks (Revisions & Proofreading) -->
    <x-dashboard.action-required :tasks="$viewModel->actionRequiredTasks()" />

    <!-- 4. Submissions Table (All Active & Archived Submissions) -->
    <x-dashboard.submissions-table :submissions="$viewModel->submissions()" />

    <!-- 5. Editorial Discussions & Author Support Links Grid (2-Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Recent Discussions (Span 2) -->
        <div class="lg:col-span-2">
            <x-dashboard.discussions-list :discussions="$viewModel->discussions()" />
        </div>

        <!-- Author Help & Guidelines Sidebar (Span 1) -->
        <div class="lg:col-span-1">
            <x-dashboard.help-sidebar :helpResources="$viewModel->helpResources()" />
        </div>
    </div>
@endsection
