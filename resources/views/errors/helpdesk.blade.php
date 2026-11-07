@extends('helpdesk.layouts.auth')
@section('title', $title)
@section('content')
<section class="section login-section"><div class="container"><div class="login-panel portal-panel"><img src="{{ asset('helpdesk/images/pmma-seal.png') }}" alt="PMMA seal" width="72" height="72"><span class="portal-eyebrow">PMMA HELPDESK · {{ $code }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p><a class="btn btn-primary" href="{{ route('home') }}">Back to Support Services</a><a href="{{ route('login') }}">Staff Login</a></div></div></section>
@endsection
