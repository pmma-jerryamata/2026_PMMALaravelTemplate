@extends('helpdesk.layouts.portal')
@section('title', 'Frequently Asked Questions')
@push('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/telephone-directory.css') }}?v={{ filemtime(public_path('helpdesk/css/telephone-directory.css')) }}">
<script src="{{ asset('helpdesk/js/telephone-directory.js') }}?v={{ filemtime(public_path('helpdesk/js/telephone-directory.js')) }}" defer></script>
@endpush
@section('content')
@include('helpdesk.portal.partials.heading', ['title' => 'How Can We Help?', 'subtitle' => 'Answers to common questions about campus support and service requests.'])
<div class="container telephone-directory-shortcut"><a href="#telephone-directory"><i class="bi bi-telephone" aria-hidden="true"></i> Looking for an office number? View the Telephone Directory <i class="bi bi-arrow-down" aria-hidden="true"></i></a></div>
<section class="section faq-section"><div class="container"><div class="faq-layout"><div class="faq-list"><div class="faq-list-heading"><span class="portal-eyebrow">HELP CENTER</span><h2>Tickets & Requests</h2><p>Select a question to view the answer.</p></div>
@foreach([
'How do I submit a ticket?' => 'Click Submit a Ticket, choose a service, select a suggested issue or write your own, then enter your PMMA email. If your email is new to the directory, provide your name and department for administrator activation. Your ticket is recorded immediately. After submission, save the ticket number shown on the confirmation page.',
'How do I check the status of my ticket?' => 'Open Track Ticket and enter your ticket number to see ticket details and the latest findings.',
'What details should I include?' => 'Describe the issue, its location, the affected system or equipment, and any troubleshooting you have tried. Do not include passwords or sensitive credentials.',
'Which service category should I choose?' => 'Select the category closest to your concern. If you are unsure, choose Others and describe the assistance you need.',
'When will my concern be resolved?' => 'Resolution depends on the type and complexity of the concern. This portal does not guarantee a fixed response time. Check your ticket status for progress.',
'Will I receive an email confirmation?' => 'The portal currently provides your ticket number on screen after submission. Email notifications depend on the configured mail service. Save or copy your reference before leaving the page.',
'Should I submit a second ticket for the same concern?' => 'Check the status of your existing request first. Keeping one ticket per concern helps avoid duplicate requests.',
] as $question => $answer)<details class="portal-faq"><summary>{{ $question }}<i class="bi bi-plus-lg" aria-hidden="true"></i></summary><p>{{ $answer }}</p></details>@endforeach</div>
<aside class="portal-cta faq-help"><div class="home-service-icon"><i class="bi bi-headset" aria-hidden="true"></i></div><div><h2>Still Need Assistance?</h2><p>Send us your concern so it can be recorded and tracked.</p></div><a href="{{ route('tickets.create') }}" class="btn btn-primary ui-action" aria-label="Submit a Ticket" title="Submit a Ticket"><x-action-icon name="send" /><span>Submit a Ticket</span></a><a class="faq-track-link" href="{{ route('track') }}">Track an Existing Ticket <i class="bi bi-arrow-right" aria-hidden="true"></i></a></aside></div></div></section>
@include('helpdesk.portal.partials.telephone-directory')
@endsection
