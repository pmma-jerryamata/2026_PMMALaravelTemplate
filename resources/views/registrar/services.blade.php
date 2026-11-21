@extends('layouts.registrar')

@section('title', 'Services')

@section('heading')
    <section class="support-intro registrar-intro" aria-labelledby="services-heading">
        <div class="container">
            <div>
                <p class="registrar-kicker">Registrar Services</p>
                <h1 id="services-heading">Request School Documents</h1>
            </div>
        </div>
    </section>
@endsection

@section('content')
    @php($contact = config('registrar.contact'))

    <div class="registrar-request-options">
        <section class="portal-panel registrar-service-panel" aria-labelledby="online-requests">
            <h2 id="online-requests">Online</h2>
            <ol class="registrar-request-steps">
                <li>Fill out the online request form. The form link will be posted here. In the meantime, <a href="mailto:{{ $contact['email'] }}">email the Registrar</a> to get the form.</li>
                <li>Wait for the Registrar’s message and fee assessment by email.</li>
                <li>Pay the required fees.</li>
                <li>Receive the requested documents by courier.</li>
            </ol>
        </section>

        <section class="portal-panel registrar-service-panel" aria-labelledby="representative-requests">
            <h2 id="representative-requests">Through a Representative</h2>
            <ol class="registrar-request-steps">
                <li>
                    Provide a Special Power of Attorney authorizing your representative or agent. Attach both documents:
                    <ul>
                        <li>A copy of the principal’s valid ID</li>
                        <li>A copy of the representative’s valid ID</li>
                    </ul>
                </li>
                <li>Pay the required fees.</li>
            </ol>
        </section>
    </div>

    <section class="portal-panel registrar-service-panel registrar-service-contact" aria-labelledby="registrar-inquiries">
        <h2 id="registrar-inquiries">Registrar Inquiries</h2>
        <ul>
            <li><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
            <li><a href="tel:{{ $contact['phone_uri'] }}">{{ $contact['phone'] }}</a></li>
            <li><a target="_blank" rel="noopener" href="{{ $contact['facebook'] }}">PMMA Office of the Registrar on Facebook</a></li>
            <li><a target="_blank" rel="noopener" href="{{ route('contact') }}">Contact form</a></li>
        </ul>
    </section>
@endsection
