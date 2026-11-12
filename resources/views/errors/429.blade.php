@extends('layouts.auth')
@section('title', 'Please wait')
@section('content')
<section class="section"><div class="container"><div class="portal-panel"><span class="portal-eyebrow">429 ? PLEASE WAIT</span><h1>Too many attempts</h1><p role="alert">{{ $message }}</p><p>Wait {{ $seconds }} seconds before trying again. Check your receipt or existing request before sending another one.</p><a class="btn btn-primary" href="{{ route('home') }}">Back to Public Portal</a></div></div></section>
@endsection
