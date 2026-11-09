@extends('layouts.portal')
@section('title', 'Privacy Notice')
@section('content')
@include('portal.partials.heading', ['title' => 'Privacy Notice', 'subtitle' => 'How information submitted through this portal is used.'])
<section class="section"><div class="container faq-container portal-panel"><h2>Information Collected</h2><p>When you submit a ticket, this portal stores your name, email address, chosen service category, subject, description, priority, ticket reference, status, and timestamps.</p><h2>Purpose</h2><p>This information is used to record and process your campus support request and allow you to check its status. Enter only the information needed to explain your concern.</p><h2>Access to Your Ticket</h2><p>The tracking page requires your ticket reference number. Keep your reference private and avoid submitting passwords, financial information, or sensitive personal records.</p><h2>Questions About Your Information</h2><p>Contact the responsible campus office for requests concerning your submitted information. You may use the <a href="{{ route('contact') }}">support page</a> to start an inquiry.</p></div></section>
@endsection
