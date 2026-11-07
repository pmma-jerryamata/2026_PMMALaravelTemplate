@extends('helpdesk.layouts.dashboard')
@section('title', 'Survey Results')
@section('content')
<section class="dashboard-heading"><h1>Survey Results</h1><p>Feedback recorded for existing support tickets.</p></section><section class="portal-panel"><div class="dashboard-table-wrap"><table class="dashboard-table" id="surveys-table" data-dashboard-table data-table-source="{{ route('dashboard.tables.surveys') }}" data-table-order="5:desc" data-empty-message="No feedback has been recorded yet."><thead><tr><th>Ticket</th><th>Service</th><th>Measure</th><th>Rating</th><th>Feedback</th><th>Submitted</th></tr></thead><tbody>@include('helpdesk.dashboard.rows.surveys', ['records' => $results])</tbody></table></div>@include('helpdesk.partials.pagination', ['paginator' => $results])</section>
@endsection
