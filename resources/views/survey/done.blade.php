@extends('helpdesk.layouts.portal')
@section('title', 'Survey')
@section('content')
<section class="section"><div class="container portal-panel receipt-panel text-center"><div class="success-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></div><h1>Service Feedback</h1><p>{{ $message ?? 'Thank you for your feedback.' }}</p><a href="{{ route('home') }}" class="btn btn-primary">Back to Support Services</a></div></section>
@endsection