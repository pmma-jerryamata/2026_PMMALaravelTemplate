@extends('helpdesk.layouts.dashboard')
@section('title', 'Accomplishment Report')
@push('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/report-editor.css') }}?v={{ filemtime(public_path('helpdesk/css/report-editor.css')) }}">
<script src="{{ asset('helpdesk/js/report-editor.js') }}?v={{ filemtime(public_path('helpdesk/js/report-editor.js')) }}" defer></script>
@endpush
@section('content')
<section class="dashboard-heading"><h1>Accomplishment Report</h1><p>Completed support requests in the official PMMA accomplishment report format.</p></section>
<section class="portal-panel">
    <form method="post" action="{{ route('report') }}" class="portal-form" id="accomplishment-report-form">
        @csrf
        <div class="adapter-filters">
            <div><label for="from">From</label><input type="date" id="from" name="from" value="{{ $from }}" required></div>
            <div><label for="to">To</label><input type="date" id="to" name="to" value="{{ $to }}" required></div>
            <div><label for="technician_id">Technician</label><select id="technician_id" name="technician_id"><option value="">All technicians</option>@foreach($technicians as $technician)<option value="{{ $technician->id }}" @selected((string) $technicianId === (string) $technician->id)>{{ $technician->name }}</option>@endforeach</select></div>
        </div>
        <p class="field-hint">Dates follow the completion/update date of completed tickets. Accomplishment text uses recorded findings, with concern details as the fallback.</p>
        <p class="field-hint">The red sidebar label below the department is hidden. The report title, period and introduction remain editable below and print only on the first page. The bottom margin matches the letterhead's top inset.</p>
        <fieldset class="report-signatories report-content-editor"><legend>Report title and introduction</legend>
            <p class="field-hint">Edit the title and introductory paragraphs below. Cambria 12 pt is fixed for printing; the official letterhead stays unchanged. Clear the editor to use the standard title and date-based introduction.</p>
            <div class="report-editor-toolbar" role="toolbar" aria-label="Text formatting" hidden>
                <button type="button" data-editor-command="bold"><b>Bold</b></button><button type="button" data-editor-command="italic"><i>Italic</i></button><button type="button" data-editor-command="underline"><u>Underline</u></button>
                <button type="button" data-editor-command="justifyLeft">Left</button><button type="button" data-editor-command="justifyCenter">Center</button><button type="button" data-editor-command="justifyRight">Right</button>
                <button type="button" id="report-html-toggle" aria-pressed="false">HTML source</button>
            </div>
            <div id="report-content-editor" contenteditable="true" role="textbox" aria-label="Report title and introduction" aria-multiline="true" hidden></div>
            <label for="content_html" class="report-source-label">HTML content</label>
            <input type="hidden" id="content_default" name="content_default" value="{{ $contentHtml === '' ? '1' : '0' }}">
            <textarea id="content_html" name="content_html" maxlength="12000" rows="8">{{ $contentHtml ?: '<p align="center"><b>ACCOMPLISHMENT REPORT</b></p><p align="center">('.Carbon\Carbon::parse($from)->format('F j, Y').' - '.Carbon\Carbon::parse($to)->format('F j, Y').')</p><p>Submitted herewith is the accomplishment report of the undersigned for the period as of '.Carbon\Carbon::parse($from)->format('F j, Y').' - '.Carbon\Carbon::parse($to)->format('F j, Y').'.</p>' }}</textarea>
        </fieldset>
        <fieldset class="report-signatories"><legend>Report Signatories</legend><p class="text-muted">Enter the names and positions for this document. These values are not saved to the database.</p><div class="form-grid">
            <div><label for="prepared_by">Prepared by</label><input id="prepared_by" name="prepared_by" maxlength="100" value="{{ $signatories['prepared_by'] }}" placeholder="Full name"></div>
            <div><label for="prepared_position">Position / Office</label><input id="prepared_position" name="prepared_position" maxlength="100" value="{{ $signatories['prepared_position'] }}" placeholder="Position, office"></div>
            <div><label for="noted_by">Noted by</label><input id="noted_by" name="noted_by" maxlength="100" value="{{ $signatories['noted_by'] }}" placeholder="Full name"></div>
            <div><label for="noted_position">Position / Office</label><input id="noted_position" name="noted_position" maxlength="100" value="{{ $signatories['noted_position'] }}" placeholder="Position, office"></div>
        </div></fieldset>
        <div class="filter-actions report-actions"><button class="btn btn-primary" type="submit">Apply Filters</button><button type="submit" class="btn btn-secondary" formaction="{{ route('report.pdf') }}" formtarget="_blank"><x-action-icon name="printer" /> Print / Preview PDF</button><button type="submit" class="btn btn-secondary" formaction="{{ route('report.pdf') }}" formtarget="_blank" name="download" value="1"><x-action-icon name="file-earmark-pdf" /> Download PDF</button></div>
        <p class="field-hint">The PDF uses the original letterhead and page size. Print the PDF at Actual size / 100% to preserve its margins and spacing.</p>
    </form>
</section>
<section class="portal-panel"><div class="section-heading-row"><div><h2>Report Entries</h2><p>Identical accomplishments are listed once per date. Table search and sorting do not change the PDF; the PDF follows the date and technician filters above.</p><p>{{ $tickets->count() }} completed {{ Str::plural('request', $tickets->count()) }} · {{ Carbon\Carbon::parse($from)->format('M j, Y') }} to {{ Carbon\Carbon::parse($to)->format('M j, Y') }}</p></div></div>
<div class="dashboard-table-wrap"><table class="dashboard-table" id="report-table" data-dashboard-table data-table-order="0:asc" data-empty-message="No completed requests in the selected period."><thead><tr><th>Date</th><th>Accomplishments</th></tr></thead><tbody>
@forelse($groups as $group)<tr><td data-order="{{ Carbon\Carbon::parse($group['date'])->toDateString() }}">{{ $group['date'] }}</td><td><ul class="report-entry-list">@foreach($group['items'] as $item)<li>{{ $item }}</li>@endforeach</ul></td></tr>@empty<tr><td colspan="2" class="empty-cell">No completed requests in the selected period.</td></tr>@endforelse
</tbody></table></div></section>
@endsection