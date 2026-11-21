@extends('layouts.registrar')

@push('page-assets')
    <link rel="stylesheet" href="{{ asset('assets/icons/bootstrap-icons.css') }}">
    @php($privacyScript = public_path('assets/js/registrar-privacy.js'))
    @if(is_file($privacyScript))
        <script src="{{ asset('assets/js/registrar-privacy.js') }}?v={{ filemtime($privacyScript) }}" defer></script>
    @endif
@endpush

@section('heading')
    <section class="support-intro registrar-intro registrar-home-intro" aria-labelledby="registrar-heading">
        <div class="container">
            <div>
                <p class="registrar-kicker">{{ config('app.name') }}</p>
                <h1 id="registrar-heading">{{ config('branding.office') }}</h1>
            </div>
        </div>
    </section>
@endsection

@section('content')
    <div class="registrar-request-options">
        <section class="portal-panel registrar-service-panel" aria-labelledby="pmma-vision">
            <h2 id="pmma-vision">Vision</h2>
            <p>By 2028, the Philippine Merchant Marine Academy shall be host to Centers of Excellence in maritime education, training, and research, sustained by a smart campus environment, producing ethical and patriotic maritime leaders and professionals capable of occupying vital positions in shipping companies, national maritime organizations, and international maritime bodies, and actively promoting maritime industry development, safety, security, and sustainability.</p>
        </section>

        <section class="portal-panel registrar-service-panel" aria-labelledby="pmma-mission">
            <h2 id="pmma-mission">Mission</h2>
            <p>The Academy is a dynamic and inclusive government maritime learning institution that provides education and training, conducts research, and undertakes needs-based extension programs to develop competent, competitive, and ethical leaders and professionals with a deep sense of patriotism, responsive to the needs of the industry, promoting sustainable economic progress and national security.</p>
        </section>
    </div>

    <section class="registrar-home-services" aria-labelledby="home-services-heading">
        <h2 id="home-services-heading">Registrar Services</h2>
        <div class="registrar-home-service-grid">
            <a target="_blank" rel="noopener" class="portal-panel registrar-home-service" href="{{ route('services', absolute: false) }}">
                <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                <h3>Request School Documents Online</h3>
                <p>See the request, payment, and courier delivery steps.</p>
            </a>
            <a target="_blank" rel="noopener" class="portal-panel registrar-home-service" href="{{ route('services', absolute: false) }}">
                <i class="bi bi-person-check" aria-hidden="true"></i>
                <h3>Request Through a Representative</h3>
                <p>Check the authorization and ID requirements for your representative.</p>
            </a>
            <a target="_blank" rel="noopener" class="portal-panel registrar-home-service" href="{{ route('contact', absolute: false) }}">
                <i class="bi bi-envelope" aria-hidden="true"></i>
                <h3>Registrar Inquiries</h3>
                <p>Ask about school documents or contact the Registrar’s office.</p>
            </a>
        </div>
    </section>

    @include('registrar.partials.privacy-policy')
@endsection
