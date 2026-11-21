@extends('layouts.auth')

@php($enrollmentPlaceholder = $enrollmentPlaceholder ?? false)
@section('title', $enrollmentPlaceholder ? 'Registration/Enrollment System' : 'Staff Login')

@section('content')
    <section class="section login-section">
        <div class="container">
            <div class="login-panel portal-panel">
                <img src="{{ asset($enrollmentPlaceholder ? 'assets/images/pmma-seal.png' : 'assets/images/app-logo.svg') }}" alt="" width="72" height="72">
                <span class="portal-eyebrow">{{ $enrollmentPlaceholder ? config('branding.office') : $systemSettings['app_name'] }}</span>
                <h1>{{ $enrollmentPlaceholder ? 'Registration/Enrollment System' : 'Welcome Back' }}</h1>
                @if($enrollmentPlaceholder)
                    <p>Registration and enrollment sign-in will be available here.</p>
                    <div class="portal-form">
                @else
                    <p>Sign In to manage support requests.</p>
                    @if($errors->any())
                        <div class="portal-alert error" role="alert">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('login.submit') }}" method="post" class="portal-form">
                        @csrf
                @endif
                    <label for="email">Username or Email</label>
                    <input id="email" type="text" name="email" value="{{ $enrollmentPlaceholder ? '' : old('email') }}" autocomplete="{{ $enrollmentPlaceholder ? 'off' : 'username' }}" required maxlength="254" @disabled($enrollmentPlaceholder)>

                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input id="password" type="password" name="password" autocomplete="{{ $enrollmentPlaceholder ? 'off' : 'current-password' }}" required @disabled($enrollmentPlaceholder)>
                        @unless($enrollmentPlaceholder)
                            <button type="button" class="password-toggle" aria-label="Show Password" aria-controls="password" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                        @endunless
                    </div>

                    <button class="btn btn-primary ui-action" type="{{ $enrollmentPlaceholder ? 'button' : 'submit' }}" aria-label="Sign In" title="Sign In" @disabled($enrollmentPlaceholder)>
                        <x-action-icon name="box-arrow-in-right" />
                        <span>Sign In</span>
                    </button>
                @if($enrollmentPlaceholder)
                    </div>
                    <p class="text-muted login-help"><a href="{{ route('home', absolute: false) }}">Back to Registrar Portal</a></p>
                @else
                    </form>
                    <p class="text-muted login-help">Staff access only. Contact your helpdesk administrator for an account.</p>
                    <a href="{{ route('home') }}">← Back to Support Services</a>
                @endif
            </div>
        </div>
    </section>
@endsection
