@extends('layouts.dashboard')
@section('title', 'Activity Logs')
@section('content')
<section class="dashboard-heading"><h1>System Activity Logs</h1><p>Dev Admin only · Public requests, authentication, and staff operations · Times in Philippine time.</p></section>
<section class="portal-panel audit-panel"><form id="audit-filters" class="dashboard-filters">
    <div><label for="audit-ip">IP Address</label><input id="audit-ip" name="ip" placeholder="Search IP" maxlength="45"></div>
    <div><label for="audit-user">User</label><input id="audit-user" name="user" placeholder="Name, username, email, or user ID" maxlength="254"></div>
    <div><label for="audit-operation">Operation</label><select id="audit-operation" name="operation"><option value="">All Operations</option>@foreach($operations as $operation)<option>{{ $operation }}</option>@endforeach</select></div>
    <div><label for="audit-outcome">Result</label><select id="audit-outcome" name="outcome"><option value="">All Results</option><option value="success">Success</option><option value="failed">Failed</option></select></div>
    <div><label for="audit-from">From</label><input type="date" id="audit-from" name="from"></div>
    <div><label for="audit-to">To</label><input type="date" id="audit-to" name="to"></div>
    <button class="btn btn-primary ui-action" type="submit"><x-action-icon name="funnel" /><span>Apply Filters</span></button><button class="btn btn-secondary ui-action" type="reset"><x-action-icon name="arrow-counterclockwise" /><span>Reset</span></button>
</form><p id="audit-error" role="alert"></p><div class="dashboard-table-wrap"><table class="dashboard-table audit-table" id="activity-log-table" data-source="{{ route('dashboard.activity-logs.data') }}"><thead><tr><th>Date / Time</th><th>User</th><th>IP Address</th><th>Operation</th><th>Result</th><th>HTTP</th><th>Details</th></tr></thead><tbody></tbody></table></div><p class="text-muted">Read-only history. Passwords, session tokens, uploaded file contents, and full request bodies are excluded.</p></section>
@endsection
