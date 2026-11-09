<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10264b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="PMMA Ticket System — submit campus service requests and track your support tickets.">
    <title>@yield('title', 'Home') | {{ $systemSettings['app_name'] }}</title>
    <link rel="icon" type="image/png" href="{{ asset('helpdesk/images/pmma-logo.png') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/accessibility.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/pmma.css') }}?v={{ filemtime(public_path('helpdesk/css/pmma.css')) }}">
    <script src="{{ asset('helpdesk/js/portal.js') }}?v={{ filemtime(public_path('helpdesk/js/portal.js')) }}" defer></script>
    <link rel="stylesheet" href="{{ asset('helpdesk/css/legacy-adapter.css') }}?v={{ $designVersion }}">
    <script src="{{ asset('helpdesk/js/legacy-adapter.js') }}?v={{ $designVersion }}" defer></script>
    @stack('page-assets')
    <link rel="stylesheet" href="{{ asset('helpdesk/css/government-footer.css') }}?v={{ filemtime(public_path('helpdesk/css/government-footer.css')) }}">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<div class="hotline-bar"><div class="container portal-topbar"><span><i class="bi bi-shield-check" aria-hidden="true"></i> Philippine Merchant Marine Academy</span><a href="{{ route('track') }}">Already submitted a request? Track Your Ticket <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div></div>
<header class="site-header"><div class="container header-inner">
    <a class="portal-brand" href="{{ route('home') }}" aria-label="PMMA Helpdesk home"><img class="brand-seal" src="{{ asset('helpdesk/images/pmma-logo.png') }}" alt="PMMA seal" width="64" height="64"><span><strong>{{ $systemSettings['app_name'] }}</strong><small>{{ $systemSettings['portal_subtitle'] }}</small></span></a>
    <nav id="main-navigation" class="main-nav" aria-label="Main navigation"><ul>
        @foreach(['home' => 'Home', 'services' => 'Services', 'queue' => 'Live Queue', 'track' => 'Track Ticket', 'faq' => 'FAQs', 'contact' => 'Contact'] as $route => $label)
        <li><a href="{{ route($route) }}" @class(['active' => request()->routeIs($route, $route.'.*')]) @if(request()->routeIs($route, $route.'.*')) aria-current="page" @endif>{{ $label }}</a></li>
        @endforeach
        <li class="nav-account"><a class="btn btn-primary nav-login ui-action" aria-label="{{ auth()->check() ? 'Dashboard' : 'Login' }}" title="{{ auth()->check() ? 'Dashboard' : 'Login' }}" href="{{ auth()->check() ? route('dashboard') : route('login') }}" @if(request()->routeIs('login', 'dashboard', 'dashboard.*')) aria-current="page" @endif><i class="bi bi-{{ auth()->check() ? 'grid' : 'box-arrow-in-right' }}" aria-hidden="true"></i><span>{{ auth()->check() ? 'Dashboard' : 'Login' }}</span></a></li>
    </ul></nav>
    <button type="button" class="mobile-menu-toggle btn btn-secondary" aria-controls="main-navigation" aria-expanded="false" aria-label="Toggle navigation"><i class="bi bi-list" aria-hidden="true"></i></button>
</div></header>
<div class="info-bar"><div class="container portal-infobar"><span><span class="status-dot"></span> {{ $systemSettings['support_tagline'] }}</span><span class="portal-clock"><span id="ph-clock" data-sync-url="{{ route('time') }}">Syncing time…</span><span class="clock-label">PST</span></span></div></div>
<main id="main-content">
@if($errors->any())<div class="container"><div class="portal-alert error" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
@yield('content')
</main>
@include('helpdesk.partials.government-footer')
<footer class="site-footer"><div class="container"><div class="footer-main-new">
    <div class="footer-brand"><a class="portal-brand footer-pmma" href="{{ route('home') }}"><img class="brand-seal" src="{{ asset('helpdesk/images/pmma-logo.png') }}" alt="PMMA seal" width="64" height="64" loading="lazy"><span><strong>{{ $systemSettings['app_name'] }}</strong><small>{{ $systemSettings['portal_subtitle'] }}</small></span></a><address class="footer-contact"><strong>{{ config('footer.organization.name') }}</strong><span>{{ config('footer.organization.address') }}</span><a href="mailto:{{ config('footer.organization.email') }}"><i class="bi bi-envelope" aria-hidden="true"></i><span>{{ config('footer.organization.email') }}</span></a><a href="tel:{{ str_replace(' ', '', config('footer.organization.telephone')) }}"><i class="bi bi-telephone" aria-hidden="true"></i><span>{{ config('footer.organization.telephone') }}</span></a></address></div>
    <div class="footer-column"><h4>Quick Links</h4><ul class="footer-links-new"><li><a href="{{ route('services') }}">Browse Services</a></li><li><a href="{{ route('tickets.create') }}">Submit a Ticket</a></li><li><a href="{{ route('track') }}">Track Your Ticket</a></li></ul></div>
    <div class="footer-column"><h4>Help & Resources</h4><ul class="footer-links-new"><li><a href="{{ route('faq') }}">Frequently Asked Questions</a></li><li><a href="{{ route('contact') }}">Contact & Support</a></li><li><a href="{{ route('privacy') }}">Privacy Notice</a></li></ul></div>
    <div class="footer-column"><h4>PMMA Service Portal</h4><p>For cadets, faculty, and staff.<br>Submit. Track. Stay informed.</p><a href="{{ route('tickets.create') }}" class="footer-contribute">How Can We Help? <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
</div><div class="footer-bottom-new"><div class="footer-copyright"><span>&copy; {{ date('Y') }} {{ $systemSettings['app_name'] }}.</span><span>{{ $systemSettings['footer_credit'] }}</span></div></div></div></footer>
</body></html>
