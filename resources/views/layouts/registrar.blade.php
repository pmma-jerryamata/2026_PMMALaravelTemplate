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
    <link rel="stylesheet" href="{{ asset('assets/css/registrar.css') }}">
    @stack('page-assets')
</head>
<body class="registrar-portal">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="registrar-header">
        <div class="container registrar-header-inner">
            <a class="portal-brand registrar-brand" href="{{ route('home') }}">
                <img class="registrar-brand-mark" src="{{ asset('assets/images/pmma-wordmark.png') }}" alt="" width="103" height="103">
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
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1">
        @yield('heading')
        <div class="container registrar-content">
            @yield('content')
        </div>
    </main>

    <footer class="registrar-footer">
        <div class="container registrar-footer-inner">
            <span>&copy; {{ now('Asia/Manila')->year }} {{ config('branding.organization') }}</span>
            <span>{{ config('branding.office') }}</span>
        </div>
    </footer>
</body>
</html>
