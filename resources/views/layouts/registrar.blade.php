<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10264b">
    <meta name="description" content="{{ config('branding.organization') }} — {{ config('branding.office') }}">
    <title>@yield('title', config('app.name')) | {{ config('branding.organization') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/accessibility.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pmma.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/registrar.css') }}?v={{ filemtime(public_path('assets/css/registrar.css')) }}">
    @php($clockScript = public_path('assets/js/philippine-time.js'))
    @if(is_file($clockScript))
        <script src="{{ asset('assets/js/philippine-time.js') }}?v={{ filemtime($clockScript) }}" defer></script>
    @endif
    <script src="{{ asset('assets/js/registrar-navigation.js') }}" defer></script>
    @stack('page-assets')
</head>
<body class="registrar-portal">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="registrar-header">
        <div class="container registrar-header-inner">
            <a class="portal-brand registrar-brand" href="{{ route('home') }}">
                <img class="registrar-brand-mark" src="{{ asset('assets/images/pmma-seal.png') }}" alt="" width="1000" height="1000">
                <span>
                    <span class="registrar-academy-name">{{ config('branding.organization') }}</span>
                    <span class="registrar-office-name">{{ config('branding.office') }}</span>
                </span>
            </a>
            <div class="registrar-header-logos">
                <img src="{{ asset('assets/images/philippine-transparency-seal.png') }}" alt="Philippine Transparency Seal" width="103" height="103">
                <img src="{{ asset('assets/images/freedom-of-information.png') }}" alt="Freedom of Information Philippines" width="103" height="103">
                <img src="{{ asset('assets/images/pmma-ims.png') }}" alt="PMMA Information Management System" width="103" height="103">
            </div>
        </div>
        <nav class="registrar-nav" aria-label="Main navigation">
            <div class="container">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('services', absolute: false) }}" @if(request()->routeIs('services')) aria-current="page" @endif>Services</a>
                <a href="{{ route('qualifications') }}" @if(request()->routeIs('qualifications')) aria-current="page" @endif>Qualifications &amp; Requirements</a>
                @if(\Illuminate\Support\Facades\Route::has('transparency.citizens-charter'))
                    <details @class(['registrar-nav-menu', 'active' => request()->routeIs('transparency.*')])>
                        <summary>Transparency</summary>
                        <div class="registrar-nav-submenu">
                            <a href="{{ route('transparency.citizens-charter', absolute: false) }}" @if(request()->routeIs('transparency.citizens-charter')) aria-current="page" @endif>Citizen’s Charter</a>
                        </div>
                    </details>
                @endif
                <a href="{{ route('faq') }}" @if(request()->routeIs('faq')) aria-current="page" @endif>FAQs</a>
            </div>
        </nav>
        @php($philippineTime = now('Asia/Manila'))
        <div class="registrar-time-bar">
            <div class="container registrar-time-inner">
                <strong>Philippine Standard Time</strong>
                <time id="ph-clock" datetime="{{ $philippineTime->toIso8601String() }}" @if(\Illuminate\Support\Facades\Route::has('time')) data-sync-url="{{ route('time') }}" @endif>{{ $philippineTime->format('M j, Y, g:i:s A') }}</time>
                <span data-ph-clock-source>Server clock</span>
            </div>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        @yield('heading')
        <div class="container registrar-content">
            @yield('content')
        </div>
    </main>

    <footer class="registrar-footer">
        @include('registrar.partials.government-footer')
        <div class="container registrar-footer-inner">
            <span>&copy; {{ now('Asia/Manila')->year }} {{ config('branding.organization') }} · {{ config('branding.office') }}</span>
            <a class="registrar-footer-contact" href="{{ route('contact', absolute: false) }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact Us</a>
        </div>
    </footer>
</body>
</html>
