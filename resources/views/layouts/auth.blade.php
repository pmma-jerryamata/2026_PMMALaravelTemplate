<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10264b">
    <title>@yield('title', 'Staff Login') | {{ $systemSettings['app_name'] }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/app-logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/accessibility.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/pmma.css') }}">
</head>
<body class="auth-page">
    <main class="auth-main" id="main-content">
        @yield('content')
    </main>
<script>
document.querySelector('.password-toggle')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    this.setAttribute('aria-pressed', String(visible));
    this.setAttribute('aria-label', visible ? 'Hide Password' : 'Show Password');
    this.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
</body>
</html>
