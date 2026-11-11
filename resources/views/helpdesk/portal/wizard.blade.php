@extends('helpdesk.layouts.portal')
@section('title', 'Submit a Ticket')
@push('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/ticket-wizard.css') }}?v={{ filemtime(public_path('helpdesk/css/ticket-wizard.css')) }}">
<script src="{{ asset('helpdesk/js/ticket-wizard.js') }}?v={{ filemtime(public_path('helpdesk/js/ticket-wizard.js')) }}" defer></script>
<link rel="stylesheet" href="{{ asset('helpdesk/css/service-choices.css') }}?v={{ filemtime(public_path('helpdesk/css/service-choices.css')) }}">
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
<div class="service-choice-heading"><span class="portal-eyebrow">STEP 1 OF 3</span><h2 id="service-choice-heading">What do you need help with?</h2><p>Choose one service to continue. You can describe your issue on the next page.</p></div>
<ul class="service-choice-grid" aria-labelledby="service-choice-heading">
@include('helpdesk.portal.partials.cards')
</ul>
@if($services->isEmpty())<p>No services are available yet. <a href="{{ route('contact') }}">Contact ITSU for assistance.</a></p>@endif
<p class="service-choice-help"><i class="bi bi-info-circle" aria-hidden="true"></i>Choose the closest match. You can write your own concern even if no suggested issue matches.</p>
@elseif($step === 'issue')
<form method="post" action="{{ route('tickets.details') }}" class="portal-panel portal-form issue-form">@csrf
<input type="hidden" name="task_id" value="{{ $serviceId }}">
<span class="portal-eyebrow">{{ $service['title'] }}</span><h2>Describe your issue</h2>
<p class="issue-intro">Start with the issue that best matches your concern.</p>
<section class="encountered-section" aria-labelledby="encountered-heading"><h3 id="encountered-heading">Most encountered issues</h3><p class="field-hint">Common concerns for this service. Select a card, then add any details you want us to know.</p>
@if($issues->isNotEmpty())
<div class="issue-suggestions" role="group" aria-labelledby="encountered-heading">
@foreach($issues as $issue)<button type="button" class="issue-card" data-issue="{{ $issue->name }}" aria-pressed="false" aria-controls="issue-editor"><i class="bi bi-chat-left-text" aria-hidden="true"></i><span class="issue-card-content"><strong>{{ $issue->name }}</strong>@if(isset($issue->reports))<small>{{ number_format($issue->reports) }} previous {{ Str::plural('ticket', $issue->reports) }}</small>@endif<span class="issue-card-action" aria-hidden="true">Select this issue <i class="bi bi-arrow-right"></i></span></span></button>@endforeach
</div>
@else<p class="issue-empty">No common issues recorded for this service yet. Choose ?Write my own issue? below.</p>@endif
</section>
<div class="manual-issue-choice"><span>Can?t find your issue?</span><button type="button" class="issue-card issue-manual-card" data-manual-issue aria-pressed="false" aria-controls="issue-editor"><i class="bi bi-pencil-square" aria-hidden="true"></i><span>Other / Write my own issue</span></button></div>
<div id="issue-editor" class="issue-editor" data-issue-editor>
<label for="support_specifics">Your concern</label><textarea id="support_specifics" name="support_specifics" rows="6" required maxlength="10000" placeholder="What happened? Include the affected device and location.">{{ old('support_specifics', (string) ($draft['task_id'] ?? '') === (string) $serviceId ? ($draft['support_specifics'] ?? '') : '') }}</textarea>
<p class="field-hint">You can edit the selected issue and add more details. Do not include passwords or sensitive personal information.</p>
</div>
<div class="wizard-actions"><a class="btn btn-secondary" href="{{ route('tickets.create') }}">Back to services</a><button type="submit" class="btn btn-primary" data-issue-next>Next: Your email <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
</form>
@else
<form method="post" action="{{ route('tickets.store') }}" class="portal-panel portal-form" data-public-email-form data-lookup-url="{{ route('tickets.requester') }}">@csrf
<input type="hidden" name="task_id" value="{{ $draft['task_id'] }}"><input type="hidden" name="support_specifics" value="{{ $draft['support_specifics'] }}"><input type="hidden" name="submission_token" value="{{ $submissionToken }}">
<h2>Your PMMA email</h2><div class="wizard-summary"><strong>{{ $service['title'] }}</strong><p>{{ $draft['support_specifics'] }}</p></div>
<label for="email">PMMA Email Address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" pattern="[^@]+@([cC][dD][tT]\.)?[pP][mM][mM][aA]\.[eE][dD][uU]\.[pP][hH]" aria-describedby="email-status">
<p id="email-status" class="field-hint" role="status" aria-live="polite">Employees: @pmma.edu.ph ? Cadets: @cdt.pmma.edu.ph. We will check the directory.</p>
<fieldset data-registration-fields><legend data-registration-title>Registration</legend><p data-registration-description>Your email domain determines the registration type. Enter your full name in ALL CAPS. Your record will stay inactive until an administrator activates it. Your ticket will still be submitted.</p>
<label for="requester-name">Full Name (ALL CAPS)</label><input id="requester-name" name="name" maxlength="255" autocomplete="name" autocapitalize="characters" value="{{ old('name') }}">
<div data-employee-department><label for="registration_department_id">Department / Office</label><select id="registration_department_id" name="registration_department_id"><option value="">Choose your department</option>@foreach($departments->reject(fn ($department) => strtoupper($department->name) === 'STUDENTS') as $department)<option value="{{ $department->id }}" @selected((string) old('registration_department_id') === (string) $department->id)>{{ strtoupper($department->name) === 'STUDENTS' ? 'STUDENTS (Cadets / Students)' : $department->name }}</option>@endforeach</select></div>
</fieldset>
<div class="wizard-actions"><a class="btn btn-secondary" href="{{ route('tickets.create', ['step' => 'issue', 'category' => $draft['task_id']]) }}">Back to issue</a><button type="submit" class="btn btn-primary">Submit Ticket <i class="bi bi-send" aria-hidden="true"></i></button></div>
</form>
@endif
</div></section>
@endsection
