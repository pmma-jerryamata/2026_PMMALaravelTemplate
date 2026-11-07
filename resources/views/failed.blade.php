@extends(auth()->check() ? 'helpdesk.layouts.dashboard' : 'helpdesk.layouts.portal')
@section('title', 'Request Could Not Be Completed')
@section('content')
<section class="section"><div class="container"><div class="portal-panel receipt-panel text-center"><div class="success-icon"><i class="bi bi-exclamation-circle" aria-hidden="true"></i></div><h1>Request could not be completed.</h1><p>Please check your details and try again. If the issue continues, contact ITSU.</p><a href="{{ auth()->check() ? route('dashboard') : route('tickets.create') }}" class="btn btn-primary">Try Again</a></div></div></section>
@endsection
