<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10264b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ $systemSettings['app_name'] }}</title>
    <link rel="icon" href="{{ asset('assets/images/app-logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/accessibility.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pmma.css') }}?v={{ filemtime(public_path('assets/css/pmma.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard-theme.css') }}?v={{ filemtime(public_path('assets/css/dashboard-theme.css')) }}">
    <script src="{{ asset('assets/vendor/datatables/jquery.min.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.min.js') }}" defer></script>
    <script src="{{ asset('assets/vendor/chartjs/chart.umd.min.js') }}" defer></script>
    <script src="{{ asset('assets/js/analytics.js') }}" defer></script>
    <script src="{{ asset('assets/js/ticket-tables.js') }}?v={{ filemtime(public_path('assets/js/ticket-tables.js')) }}" defer></script>
    <script src="{{ asset('assets/js/dashboard.js') }}?v={{ filemtime(public_path('assets/js/dashboard.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset('assets/css/shared-components.css') }}?v={{ filemtime(public_path('assets/css/shared-components.css')) }}">
</head>
<body class="admin-workspace">
<a class="skip-link" href="#main-content">Skip to main content</a>
<aside id="staff-sidebar" class="admin-sidebar" aria-label="Staff sidebar">
    <a class="admin-brand" href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/app-logo.svg') }}" width="48" height="48" alt="APP seal"><span><strong>{{ $systemSettings['app_name'] }}</strong><small>STAFF WORKSPACE</small></span></a>
    <button type="button" class="admin-menu-close" aria-label="Close navigation"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    <nav id="staff-navigation" aria-label="Staff navigation">
        <span class="admin-nav-label">WORKSPACE</span>
        <a href="{{ route('dashboard') }}" @class(['selected' => request()->routeIs('dashboard') && !request('status')])><i class="bi bi-grid-1x2" aria-hidden="true"></i> Overview</a>
        <details class="admin-nav-group" @if(request('status') || request()->routeIs('dashboard.tickets.*')) open @endif><summary><i class="bi bi-inbox" aria-hidden="true"></i><span>Tickets</span><i class="bi bi-chevron-down nav-group-arrow" aria-hidden="true"></i></summary><div class="admin-nav-children">
        <a href="{{ route('dashboard', ['status' => 'Open']) }}" @class(['selected' => request('status') === 'Open'])><i class="bi bi-inbox" aria-hidden="true"></i> Open Tickets</a>
        <a href="{{ route('dashboard', ['status' => 'In Progress']) }}" @class(['selected' => request('status') === 'In Progress'])><i class="bi bi-arrow-repeat" aria-hidden="true"></i> In Progress</a>
        <a href="{{ route('dashboard', ['status' => 'Resolved']) }}" @class(['selected' => request('status') === 'Resolved'])><i class="bi bi-check2-circle" aria-hidden="true"></i> Resolved Tickets</a>
        <a href="{{ route('dashboard', ['status' => 'Closed']) }}" @class(['selected' => request('status') === 'Closed'])><i class="bi bi-archive" aria-hidden="true"></i> Closed Tickets</a>
        </div></details>
        <details class="admin-nav-group" @if(request()->routeIs('dashboard.settings')) open @endif><summary><i class="bi bi-gear" aria-hidden="true"></i><span>System Settings</span><i class="bi bi-chevron-down nav-group-arrow" aria-hidden="true"></i></summary><div class="admin-nav-children">
        @foreach(['general' => 'General / Branding', 'departments' => 'Departments', 'technicians' => 'Technicians', 'services' => 'Services', 'account' => 'My Account'] as $tab => $label)
        <a href="{{ route('dashboard.settings') }}#{{ $tab }}">{{ $label }}</a>
        @endforeach
        </div></details>
        @can('view-activity-logs')
        <a href="{{ route('dashboard.activity-logs') }}" @class(['selected' => request()->routeIs('dashboard.activity-logs*')])><i class="bi bi-journal-text" aria-hidden="true"></i> Activity Logs</a>
        @endcan
        <hr class="admin-nav-divider"><span class="admin-nav-label">RESOURCES</span>
        <a href="{{ route('faq') }}"><i class="bi bi-book" aria-hidden="true"></i> Help &amp; Guide</a>
        <a href="{{ route('home') }}"><i class="bi bi-house" aria-hidden="true"></i> Public Portal</a>
    </nav>
    <div class="admin-sidebar-bottom"><span>Information Technology Services Unit</span></div>
</aside>
<button type="button" class="admin-menu-backdrop" aria-label="Close navigation" tabindex="-1" hidden></button>
<div class="admin-shell">
    <header class="admin-topbar"><button type="button" class="admin-menu-toggle" aria-label="Open navigation" aria-controls="staff-sidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button><div><span class="admin-topbar-label">APP / Support Operations</span><strong>@yield('title', 'Dashboard')</strong></div><div class="admin-account">@php($avatarProfile = auth()->user()->technician) @if($avatarProfile?->photo_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($avatarProfile->photo_path))<img class="admin-avatar admin-avatar-photo" src="{{ route('dashboard.technicians.photo', $avatarProfile) }}?v={{ $avatarProfile->updated_at->timestamp }}" alt="{{ auth()->user()->name }} profile photo" width="36" height="36">@else<span class="admin-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>@endif<span class="admin-account-name">{{ auth()->user()->name }}<small>Helpdesk Staff</small></span><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="staff-logout ui-action" aria-label="Logout" title="Logout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Logout</span></button></form></div></header>
    <main id="main-content" class="admin-content">
        @if($errors->any())<div class="portal-alert error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
        <p id="dashboard-notice" role="status"></p>
    </main>
</div>
<dialog id="editor-dialog" class="admin-dialog" aria-labelledby="editor-title"><div class="dialog-heading"><h2 id="editor-title">Edit Record</h2><button type="button" data-close-dialog aria-label="Close Dialog" title="Close Dialog"><x-action-icon name="x-lg" /></button></div><form id="editor-form" class="portal-form"><div id="editor-errors" role="alert"></div><div id="editor-fields" class="form-grid"></div><div class="dialog-actions"><button type="button" class="btn btn-secondary ui-action" data-close-dialog aria-label="Cancel" title="Cancel"><x-action-icon name="x-lg" /><span>Cancel</span></button><button type="submit" class="btn btn-primary ui-action" aria-label="Save Changes" title="Save Changes"><x-action-icon name="floppy" /><span>Save Changes</span></button></div></form></dialog>
@stack('dashboard-data')
<dialog id="timeline-dialog" class="admin-dialog" aria-labelledby="timeline-title"><div class="dialog-heading"><h2 id="timeline-title">Ticket Timeline</h2><button type="button" data-close-timeline aria-label="Close Timeline" title="Close Timeline"><x-action-icon name="x-lg" /></button></div><div id="timeline-content" aria-live="polite"></div><div class="dialog-actions"><button type="button" class="btn btn-secondary ui-action" data-close-timeline>Close</button></div></dialog>
@include('partials.back-to-top')
</body>
</html>
