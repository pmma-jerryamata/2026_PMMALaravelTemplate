<!doctype html>
<html lang="@yield('language', 'en')"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#10264b"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Dashboard') | {{ $systemSettings['app_name'] }}</title><link rel="icon" href="{{ asset('helpdesk/images/pmma-logo.png') }}">
@foreach(['style', 'accessibility', 'pmma', 'dashboard-theme', 'legacy-adapter'] as $sheet)<link rel="stylesheet" href="{{ asset('helpdesk/css/'.$sheet.'.css') }}?v={{ $designVersion }}">@endforeach
<link rel="stylesheet" href="{{ asset('helpdesk/icons/bootstrap-icons.css') }}"><script src="{{ asset('helpdesk/vendor/chartjs/chart.umd.min.js') }}" defer></script><script src="{{ asset('helpdesk/js/legacy-adapter.js') }}?v={{ $designVersion }}" defer></script>@stack('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/vendor/datatables/dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('helpdesk/css/dashboard-tables.css') }}?v={{ $designVersion }}">
<script src="{{ asset('helpdesk/vendor/datatables/jquery.min.js') }}" defer></script>
<script src="{{ asset('helpdesk/vendor/datatables/dataTables.min.js') }}" defer></script>
<script src="{{ asset('helpdesk/js/dashboard-tables.js') }}?v={{ $designVersion }}" defer></script>
</head><body class="admin-workspace"><div id="staff-app">
<a class="skip-link" href="#main-content">Skip to main content</a>
<aside id="staff-sidebar" class="admin-sidebar" aria-label="Staff sidebar">
<a class="admin-brand" href="{{ route('dashboard') }}"><img src="{{ asset('helpdesk/images/pmma-logo.png') }}" width="48" height="48" alt="PMMA seal"><span><strong>{{ $systemSettings['app_name'] }}</strong><small>STAFF WORKSPACE</small></span></a>
<span class="admin-nav-label">WORKSPACE</span><button type="button" class="admin-menu-close" aria-label="Close navigation"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
<nav id="staff-navigation" aria-label="Staff navigation">
<a href="{{ route('dashboard') }}" @class(['selected' => request()->routeIs('dashboard', 'admin.index', 'support', 'tech.index') && !request('status')])><i class="bi bi-grid-1x2" aria-hidden="true"></i> Overview</a>
@foreach(['Pending' => 'inbox', 'Completed' => 'check2-circle', 'Cancelled' => 'archive'] as $status => $icon)<a href="{{ route('dashboard', ['status' => $status]) }}" @class(['selected' => request('status') === $status])><i class="bi bi-{{ $icon }}" aria-hidden="true"></i> {{ $status }} Tickets</a>@endforeach
<a href="{{ route('dashboard.queue') }}" @class(['selected' => request()->routeIs('dashboard.queue')])><i class="bi bi-kanban" aria-hidden="true"></i> Ticket Queue</a>
<a href="{{ route('report') }}" @class(['selected' => request()->routeIs('report')])><i class="bi bi-bar-chart" aria-hidden="true"></i> Reports</a>
<a href="{{ route('dashboard.surveys') }}" @class(['selected' => request()->routeIs('dashboard.surveys')])><i class="bi bi-star" aria-hidden="true"></i> Survey Results</a>
<a href="{{ route('dashboard.settings') }}" @class(['selected' => request()->routeIs('dashboard.settings*')])><i class="bi bi-gear" aria-hidden="true"></i> System Settings</a>
<a href="{{ route('dashboard.user-guide') }}" @class(['selected' => request()->routeIs('dashboard.user-guide')])><i class="bi bi-book" aria-hidden="true"></i> User Guide</a>
<a href="{{ route('home') }}"><i class="bi bi-house" aria-hidden="true"></i> Public Portal</a>
<a href="{{ route('tech.index') }}"><i class="bi bi-tools" aria-hidden="true"></i> Technician Dashboard</a>
</nav><div class="admin-sidebar-bottom"><span>Information Technology Services Unit</span></div></aside>
<button type="button" class="admin-menu-backdrop" aria-label="Close navigation" tabindex="-1" hidden></button>
<div class="admin-shell"><header class="admin-topbar"><button type="button" class="admin-menu-toggle" aria-label="Open navigation" aria-controls="staff-sidebar" aria-expanded="false"><i class="bi bi-list" aria-hidden="true"></i></button><div><span class="admin-topbar-label">PMMA / Support Operations</span><strong>@yield('title', 'Dashboard')</strong></div><div class="admin-account"><span class="admin-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><a href="{{ route('profile.edit') }}" class="admin-account-name">{{ auth()->user()->name }}<small>Helpdesk Staff</small></a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="staff-logout ui-action" aria-label="Logout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Logout</span></button></form></div></header>
<main id="main-content" class="admin-content">@if(session('success'))<div class="portal-alert dashboard-success" role="status">{{ session('success') }}</div>@endif @if($errors->any())<div class="portal-alert error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</main>
</div></div></body></html>
