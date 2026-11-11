@extends('helpdesk.layouts.portal')
@section('title', 'Submit a Ticket')
@push('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/ticket-wizard.css') }}?v={{ filemtime(public_path('helpdesk/css/ticket-wizard.css')) }}">
<script src="{{ asset('helpdesk/js/ticket-wizard.js') }}?v={{ filemtime(public_path('helpdesk/js/ticket-wizard.js')) }}" defer></script>
@endpush
@section('content')
@include('helpdesk.portal.partials.heading', ['title' => 'Submit a Ticket', 'subtitle' => 'A few simple steps to request assistance.'])
<section class="section"><div class="container ticket-wizard">
<ol class="ticket-steps" aria-label="Submission progress">
@foreach(['services' => 'Choose a service', 'issue' => 'Describe the issue', 'email' => 'Your PMMA email'] as $key => $label)
<li @if($step === $key) aria-current="step" @endif><span>{{ $loop->iteration }}</span>{{ $label }}</li>
@endforeach
</ol>
@if($step === 'services')
<h2>What do you need help with?</h2><p>Choose the service closest to your concern.</p>
<div class="home-services-grid">
@forelse($services as $id => $item)
<a class="home-service-card" href="{{ route('tickets.create', ['step' => 'issue', 'category' => $id]) }}"><div class="home-service-icon"><i class="bi bi-{{ $item['icon'] }}" aria-hidden="true"></i></div><div><h3>{{ $item['title'] }}</h3><p>{{ $item['description'] }}</p></div><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
@empty<p>No services are available yet. Please contact ITSU.</p>@endforelse
</div>
@elseif($step === 'issue')
<form method="post" action="{{ route('tickets.details') }}" class="portal-panel portal-form">@csrf
<input type="hidden" name="task_id" value="{{ $serviceId }}">
<span class="portal-eyebrow">{{ $service['title'] }}</span><h2>Describe your issue</h2>
<p>Choose a suggested issue to start, or type your own description. You can edit any suggestion.</p>
<div class="issue-suggestions" aria-label="Suggested issues">
@forelse($issues as $issue)<button type="button" class="issue-card" data-issue="{{ $issue->name }}" aria-pressed="false"><i class="bi bi-chat-left-text" aria-hidden="true"></i><span>{{ $issue->name }}</span></button>
@empty<p class="text-muted">No suggested issues for this service yet. Describe your concern below.</p>@endforelse
<button type="button" class="issue-card" data-manual-issue aria-pressed="false"><i class="bi bi-pencil-square" aria-hidden="true"></i><span>Other / Write my own issue</span></button>
</div>
<label for="support_specifics">Describe Your Concern</label><textarea id="support_specifics" name="support_specifics" rows="6" required maxlength="10000" placeholder="What happened? Include the affected device and location.">{{ old('support_specifics', (string) ($draft['task_id'] ?? '') === (string) $serviceId ? ($draft['support_specifics'] ?? '') : '') }}</textarea>
<p class="text-muted">Do not include passwords or sensitive personal information.</p>
<div class="wizard-actions"><a class="btn btn-secondary" href="{{ route('tickets.create') }}">Back to services</a><button type="submit" class="btn btn-primary">Next: Your email <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
</form>
@else
<form method="post" action="{{ route('tickets.store') }}" class="portal-panel portal-form" data-public-email-form data-lookup-url="{{ route('tickets.requester') }}">@csrf
<input type="hidden" name="task_id" value="{{ $draft['task_id'] }}"><input type="hidden" name="support_specifics" value="{{ $draft['support_specifics'] }}"><input type="hidden" name="submission_token" value="{{ $submissionToken }}">
<h2>Your PMMA email</h2><div class="wizard-summary"><strong>{{ $service['title'] }}</strong><p>{{ $draft['support_specifics'] }}</p></div>
<label for="email">PMMA Email Address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" pattern="[^@]+@[pP][mM][mM][aA]\.[eE][dD][uU]\.[pP][hH]" aria-describedby="email-status">
<p id="email-status" class="field-hint" role="status" aria-live="polite">Use your @pmma.edu.ph email. We will check the directory.</p>
<fieldset data-registration-fields><legend>New to the directory?</legend><p>If your email is not registered, enter your details. Your employee record will stay inactive until an administrator reviews and activates it. Your ticket will still be submitted.</p>
<label for="requester-name">Full Name</label><input id="requester-name" name="name" maxlength="255" autocomplete="name" value="{{ old('name') }}">
<label for="registration_department_id">Department / Office</label><select id="registration_department_id" name="registration_department_id"><option value="">Choose your department</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) old('registration_department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>
</fieldset>
<div class="wizard-actions"><a class="btn btn-secondary" href="{{ route('tickets.create', ['step' => 'issue', 'category' => $draft['task_id']]) }}">Back to issue</a><button type="submit" class="btn btn-primary">Submit Ticket <i class="bi bi-send" aria-hidden="true"></i></button></div>
</form>
@endif
</div></section>
@endsection
