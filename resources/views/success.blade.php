@extends(auth()->check() ? 'helpdesk.layouts.dashboard' : 'helpdesk.layouts.portal')
@section('title', 'Request Saved')
@section('content')
<section class="section"><div class="container"><div class="portal-panel receipt-panel text-center"><div class="success-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></div><h1>Request saved successfully.</h1><p>Your support request has been recorded.</p><a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="btn btn-primary">{{ auth()->check() ? 'Back to Dashboard' : 'Back to Support Services' }}</a></div></div></section>
@endsection