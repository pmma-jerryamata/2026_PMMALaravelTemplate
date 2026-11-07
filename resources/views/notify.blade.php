@extends('helpdesk.layouts.portal')
@section('title', 'Ticket Submitted')
@section('content')
<section class="section"><div class="container"><div class="portal-panel receipt-panel text-center"><div class="success-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></div><h1>Your ticket has been submitted.</h1><p>Keep this number to check the status of your request.</p><div class="ticket-reference">{{ $ticket }}</div><a href="{{ route('track', ['reference' => $ticket]) }}" class="btn btn-primary">Track Your Ticket</a></div></div></section>
@endsection