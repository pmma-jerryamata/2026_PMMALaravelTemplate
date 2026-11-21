@extends('layouts.registrar')

@section('title', 'Contact Us')

@section('heading')
    <section class="support-intro registrar-intro" aria-labelledby="contact-heading">
        <div class="container">
            <div>
                <h1 id="contact-heading">Contact Us</h1>
            </div>
        </div>
    </section>
@endsection

@section('content')
    @php($contact = config('registrar.contact'))

    <div class="registrar-contact-grid">
        <section class="portal-panel registrar-service-panel" aria-labelledby="contact-details-heading">
            <h2 id="contact-details-heading">Office of the Registrar</h2>
            <address class="registrar-contact-details">
                <dl>
                    <dt>Address</dt>
                    <dd>{{ $contact['address'] }}</dd>
                    <dt>Phone</dt>
                    <dd><a href="tel:{{ $contact['phone_uri'] }}">{{ $contact['phone'] }}</a></dd>
                    <dt>Email</dt>
                    <dd><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></dd>
                    <dt>Website</dt>
                    <dd><a target="_blank" rel="noopener" href="{{ $contact['website'] }}">pmma.edu.ph</a></dd>
                    <dt>Facebook</dt>
                    <dd><a target="_blank" rel="noopener" href="{{ $contact['facebook'] }}">PMMA - Office of the Registrar</a></dd>
                </dl>
            </address>
        </section>

        <section class="portal-panel registrar-service-panel" aria-labelledby="contact-form-heading">
            <h2 id="contact-form-heading">Send a Message</h2>

            @if(session('status'))
                <p class="registrar-contact-success" role="status">{{ session('status') }}</p>
            @endif

            @error('contact')
                <div class="portal-alert error" role="alert">{{ $message }}</div>
            @enderror

            @isset($contactCooldown)
                <div class="portal-alert error" role="alert">{{ $contactCooldown }}</div>
            @endisset

            @if(config('mail.default') !== 'smtp' && ! $errors->has('contact'))
                <p>Online messaging is currently unavailable. Please email or call the Registrar.</p>
            @endif

            <form class="portal-form registrar-contact-form" action="{{ route('contact.send') }}" method="post">
                @csrf

                <label for="contact-name">Full Name</label>
                <input id="contact-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                @error('name')
                    <p id="contact-name-error" class="registrar-field-error">{{ $message }}</p>
                @enderror

                <label for="contact-email">Email Address</label>
                <input id="contact-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="254" required @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                @error('email')
                    <p id="contact-email-error" class="registrar-field-error">{{ $message }}</p>
                @enderror

                <label for="contact-message">Message</label>
                <textarea id="contact-message" name="message" rows="6" maxlength="3000" required @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
                @error('message')
                    <p id="contact-message-error" class="registrar-field-error">{{ $message }}</p>
                @enderror

                <button class="btn btn-primary ui-action" type="submit" @disabled(config('mail.default') !== 'smtp')>Send Message</button>
            </form>
        </section>
    </div>
@endsection
