<!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#10264b">
<title>Job Request | {{ $systemSettings['app_name'] }}</title>
<link rel="icon" href="{{ asset('helpdesk/images/pmma-logo.png') }}">
<link rel="stylesheet" href="{{ asset('helpdesk/icons/bootstrap-icons.css') }}">
<link rel="stylesheet" href="{{ asset('helpdesk/css/technician.css') }}?v={{ filemtime(public_path('helpdesk/css/technician.css')) }}">
</head><body>
<a class="tech-skip" href="#main-content">Skip to tickets</a>
<header class="tech-header"><a class="tech-brand" href="{{ route('tech.index') }}"><img src="{{ asset('helpdesk/images/pmma-logo.png') }}" alt="" width="40" height="40"><span>PMMA Helpdesk<small>TECHNICIAN WORKSPACE</small></span></a><nav aria-label="Workspace"><a href="{{ route('dashboard') }}">Admin panel</a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form></nav></header>
<main id="main-content" class="tech-main">
<section class="tech-heading"><div><p class="tech-eyebrow">{{ auth()->user()->name }}</p><h1>Job Request</h1><p>Review the concern, record your diagnosis, and update the ticket.</p></div><div class="tech-heading-actions"><a class="tech-button tech-create" href="{{ route('dashboard.tickets.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Create Ticket</a><a class="tech-button tech-secondary" href="{{ route('tech.index', request()->query()) }}"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh</a></div></section>
@if(session('success'))<div class="tech-alert" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="tech-alert tech-error" role="alert"><strong>Changes were not saved.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="tech-counts"><a href="{{ route('tech.index') }}"><strong>{{ $pendingCount }}</strong><span>Pending tickets</span></a><a href="{{ route('tech.index', ['technician' => 'unassigned']) }}"><strong>{{ $unassignedCount }}</strong><span>Unassigned</span></a></div>
<details class="tech-filter-panel">
<summary><span><i class="bi bi-funnel" aria-hidden="true"></i> Search &amp; Filters</span><span class="tech-filter-toggle"><span class="tech-filter-open-label">Show</span><span class="tech-filter-close-label">Hide</span><i class="bi bi-chevron-down" aria-hidden="true"></i></span></summary>
<form method="get" action="{{ route('tech.index') }}" class="tech-filters" aria-label="Filter tickets">
<div class="tech-search"><label for="ticket-search">Search tickets</label><input id="ticket-search" type="search" name="q" maxlength="150" value="{{ request('q') }}" placeholder="Ticket no., concern, requester or office"></div>
<div><label for="filter-status">Status</label><select id="filter-status" name="status">@foreach(array_merge(['all' => 'All statuses'], array_combine($statuses, $statuses)) as $value => $label)<option value="{{ $value }}" @selected($activeStatus === $value)>{{ $label }}</option>@endforeach</select></div>
<div><label for="filter-technician">Technician</label><select id="filter-technician" name="technician"><option value="">All technicians</option><option value="unassigned" @selected(request('technician') === 'unassigned')>Unassigned</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) request('technician') === (string) $technician->id)>{{ $technician->name }}</option>@endforeach</select></div>
@if($priorityEnabled)<div><label for="filter-priority">Priority</label><select id="filter-priority" name="priority"><option value="">All priorities</option>@foreach($priorities as $priority)<option @selected(request('priority') === $priority)>{{ $priority }}</option>@endforeach</select></div>@endif
<div class="tech-filter-actions"><button class="tech-button" type="submit">Apply filters</button><a href="{{ route('tech.index') }}">Reset</a></div>
</form>
</details>
<div class="tech-list-heading"><h2>{{ $activeStatus === 'Pending' ? 'Available tickets' : ($activeStatus === 'all' ? 'All tickets' : $activeStatus.' tickets') }} <span>{{ $tickets->total() }}</span></h2><p>{{ $priorityEnabled ? 'Highest priority first · Oldest first within each priority' : 'Oldest tickets first' }}</p></div>
<div class="tech-ticket-list">
@forelse($tickets as $ticket)
@php($editing = (string) old('editing_ticket') === (string) $ticket->id)
<article class="tech-ticket" id="ticket-{{ $ticket->id }}" aria-labelledby="ticket-title-{{ $ticket->id }}">
<div class="tech-ticket-top"><span class="tech-reference">#{{ $ticket->id }}</span><div class="tech-badges"><span class="tech-badge" data-priority="{{ $ticket->priority ?? 'Normal' }}">{{ $ticket->priority ?? 'Normal' }} priority</span><span class="tech-badge" data-status="{{ $ticket->status }}">{{ $ticket->status }}</span></div></div>
<h3 id="ticket-title-{{ $ticket->id }}">{{ optional($ticket->task)->name ?? 'Support request' }}</h3>
<p class="tech-concern">{{ $ticket->support_specifics ?: 'No concern details provided.' }}</p>
<dl class="tech-meta"><div><dt>Requester / Office</dt><dd>{{ optional($ticket->employee)->name ?? 'Unavailable requester' }}<span>{{ optional(optional($ticket->employee)->department)->name ?? 'Not specified' }}</span></dd></div><div><dt>Assigned to</dt><dd>{{ optional($ticket->technician)->name ?? 'Unassigned' }}</dd></div><div><dt>Submitted</dt><dd><time datetime="{{ optional($ticket->created_at)->toIso8601String() }}">{{ optional($ticket->created_at)->format('M d, Y · h:i A') }}</time></dd></div></dl>
@if($ticket->findings)<div class="tech-findings"><strong>Latest diagnosis / action</strong><p>{{ $ticket->findings }}</p></div>@endif
<details class="tech-editor" @if($editing) open @endif><summary><span><i class="bi bi-pencil-square" aria-hidden="true"></i> Diagnosis &amp; update</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
<form method="post" action="{{ route('tech.tickets.update', $ticket->id) }}">@csrf
<input type="hidden" name="editing_ticket" value="{{ $ticket->id }}">
@foreach(['status' => $activeStatus, 'technician' => request('technician'), 'priority' => request('priority'), 'q' => request('q')] as $name => $value)<input type="hidden" name="view_{{ $name }}" value="{{ $value }}">@endforeach
<label for="findings-{{ $ticket->id }}">Diagnosis / Findings / Action Taken</label><textarea id="findings-{{ $ticket->id }}" name="findings" maxlength="1023" rows="5" placeholder="Record what you found and what you did.">{{ $editing ? old('findings') : $ticket->findings }}</textarea>
<fieldset class="tech-status-picker" aria-describedby="status-help-{{ $ticket->id }}"><legend>Ticket status</legend>
<p id="status-help-{{ $ticket->id }}">Select the current status, then save your update.</p>
<div class="tech-status-options">@foreach($statuses as $status)<label class="tech-status-option" data-status="{{ $status }}" for="status-{{ $ticket->id }}-{{ $loop->index }}"><input type="radio" id="status-{{ $ticket->id }}-{{ $loop->index }}" name="status" value="{{ $status }}" required @checked(($editing ? old('status') : $ticket->status) === $status)><span><strong>{{ $status }}</strong><small>{{ ['Pending' => 'Still needs attention', 'Completed' => 'Issue has been resolved', 'Cancelled' => 'Request will not proceed'][$status] }}</small></span></label>@endforeach</div>
</fieldset>
<div @class(['tech-editor-grid', 'tech-editor-grid-single' => !$priorityEnabled])>
@if($priorityEnabled)<div><label for="priority-{{ $ticket->id }}">Priority</label><select id="priority-{{ $ticket->id }}" name="priority" required>@foreach($priorities as $priority)<option @selected(($editing ? old('priority') : ($ticket->priority ?? 'Normal')) === $priority)>{{ $priority }}</option>@endforeach</select></div>@endif
<div class="tech-assignment"><label for="technician-{{ $ticket->id }}">Assigned Technician</label><select id="technician-{{ $ticket->id }}" name="technician_id"><option value="">Unassigned</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) ($editing ? old('technician_id') : $ticket->technician_id) === (string) $technician->id)>{{ $technician->name }}</option>@endforeach</select></div></div>
<button type="submit" class="tech-button tech-save"><i class="bi bi-check2" aria-hidden="true"></i> Save ticket update</button>
</form></details>
</article>
@empty<div class="tech-empty"><i class="bi bi-check2-circle" aria-hidden="true"></i><h3>No tickets in this view</h3><p>Try another status or reset your filters.</p><a class="tech-button tech-secondary" href="{{ route('tech.index') }}">View pending tickets</a></div>@endforelse
</div>
@if($tickets->hasPages())<nav class="tech-pagination" aria-label="Ticket pages">@if($tickets->onFirstPage())<span>Previous</span>@else<a class="tech-button tech-secondary" href="{{ $tickets->previousPageUrl() }}">Previous</a>@endif<span>Page {{ $tickets->currentPage() }} of {{ $tickets->lastPage() }}</span>@if($tickets->hasMorePages())<a class="tech-button tech-secondary" href="{{ $tickets->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif</nav>@endif
</main>@include('helpdesk.partials.back-to-top')
</body></html>
