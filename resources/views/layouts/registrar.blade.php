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
    <link rel="stylesheet" href="{{ asset('assets/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pmma.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/registrar.css') }}">
    @stack('page-assets')
</head>
<body class="registrar-portal">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="registrar-header">
        <div class="container registrar-header-inner">
            <a class="portal-brand registrar-brand" href="{{ route('home') }}">
                <span class="registrar-brand-mark" aria-hidden="true"><i class="bi bi-mortarboard"></i></span>
                <span>{{ config('branding.organization') }}</span>
            </a>
            <nav class="registrar-nav" aria-label="Main navigation">
                <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a>
            </nav>
        </div>
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
