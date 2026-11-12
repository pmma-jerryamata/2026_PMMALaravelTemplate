@extends('helpdesk.layouts.dashboard')
@section('title', 'Reports')
@push('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/reports.css') }}?v={{ filemtime(public_path('helpdesk/css/reports.css')) }}">
<script src="{{ asset('helpdesk/js/job-report-editor.js') }}?v={{ filemtime(public_path('helpdesk/js/job-report-editor.js')) }}" defer></script>
@endpush
@section('content')
<section class="dashboard-heading"><h1>Reports</h1><p>Compare job requests by office and service type, then preview or print on the PMMA template.</p></section>
@include('helpdesk.dashboard.report-tabs')
<section class="portal-panel"><form id="job-report-form" method="post" action="{{ route('report.jobs') }}" class="portal-form report-filters">@csrf
<div class="adapter-filters">
<div><label for="report-from">From</label><input id="report-from" type="date" name="from" value="{{ $from }}"></div>
<div><label for="report-to">To</label><input id="report-to" type="date" name="to" value="{{ $to }}"></div>
<div><label for="report-technician">Technician</label><select id="report-technician" name="technician_id"><option value="">All technicians</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) ($filters['technician_id'] ?? '') === (string) $technician->id)>{{ $technician->name }}</option>@endforeach</select></div>
<div><label for="report-office">Office / Department</label><select id="report-office" name="department_id"><option value="">All offices</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select></div>
<div><label for="report-service">Service Type</label><select id="report-service" name="task_id"><option value="">All service types</option>@foreach($services as $service)<option value="{{ $service->id }}" @selected((string) ($filters['task_id'] ?? '') === (string) $service->id)>{{ $service->name }}</option>@endforeach</select></div>
<div><label for="report-status">Status</label><select id="report-status" name="status"><option value="">All statuses</option>@foreach(['Pending','Completed','Cancelled'] as $status)<option @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>@endforeach</select></div>
</div>
<label class="report-all-dates"><input type="checkbox" name="all_dates" value="1" @checked(!empty($filters['all_dates']))> All dates (ignore date range)</label>
<p class="field-hint">Counts use the ticket submission date. All statuses are included unless you select one. Offices with no requests remain visible.</p>
<details class="report-options" open><summary>Report title and introduction</summary><label for="job-report-title">Report title</label><input id="job-report-title" name="report_title" maxlength="150" value="{{ $reportTitle }}"><input type="hidden" name="intro_default" value="{{ $filters['intro_default'] }}"><label for="job-introduction">Introduction</label><textarea id="job-introduction" name="introduction" maxlength="3000" rows="4">{{ $introduction }}</textarea><p class="field-hint">The date line follows the date filters. The initial introduction follows your filters; manual edits are preserved.</p></details>
<div class="report-toolbar"><button class="btn btn-primary" type="submit">Apply Filters</button><a class="btn btn-secondary" href="{{ route('report.jobs') }}">Reset</a><button type="submit" class="btn btn-secondary" formaction="{{ route('report.jobs.pdf') }}" formtarget="jobs-preview" data-jobs-preview>Update PDF Preview</button><button type="submit" class="btn btn-secondary" formaction="{{ route('report.jobs.pdf') }}" formtarget="_blank" name="download" value="1">Download PDF</button></div>
</form></section>
<section class="portal-panel"><div class="report-summary-heading"><div><h2>{{ $reportTitle }}</h2><p>{{ implode(' · ', $scope) }}</p></div><div class="report-total"><strong>{{ number_format($total) }}</strong><span>Total requests</span></div></div>
<div class="report-matrix-scroll" tabindex="0" role="region" aria-label="Requests by office and service. Scroll horizontally to view all service columns."><table class="report-matrix"><caption class="sr-only">Job requests by department and service type</caption><thead><tr><th scope="col">Department / Unit</th>@foreach($reportServices as $service)<th scope="col">{{ $service->name }}</th>@endforeach<th scope="col" class="report-total-cell">Total</th></tr></thead><tbody>
@forelse($rows as $row)<tr><th scope="row">{{ $row['name'] }}</th>@foreach($reportServices as $service)<td>{{ number_format($row['counts'][$service->id]) }}</td>@endforeach<td class="report-total-cell">{{ number_format($row['total']) }}</td></tr>@empty<tr><td colspan="{{ $reportServices->count() + 2 }}">No offices available.</td></tr>@endforelse
</tbody><tfoot><tr><th scope="row">TOTAL</th>@foreach($reportServices as $service)<td>{{ number_format($columnTotals[$service->id]) }}</td>@endforeach<td class="report-total-cell">{{ number_format($total) }}</td></tr></tfoot></table></div></section>
<section class="portal-panel report-preview-panel"><h2>PDF Preview</h2><p>Landscape layout keeps every service column together. The official header and left sidebar are fitted proportionally to the sheet. Additional pages are used only when office rows exceed the available height.</p><iframe class="report-pdf-viewer" name="jobs-preview" title="Job requests report PDF preview"></iframe><p>Use the PDF viewer's print button to print at Actual size / 100%.</p></section>
@endsection
