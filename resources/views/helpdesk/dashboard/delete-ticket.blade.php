@extends('helpdesk.layouts.dashboard')
@section('title', 'Delete Ticket #'.$ticket->id)
@section('content')
<section class="portal-panel ticket-delete-panel">
    <span class="portal-eyebrow">TICKET #{{ $ticket->id }}</span>
    <h1>Delete this ticket permanently?</h1>
    <div class="portal-alert error" role="note">This removes the ticket and its survey feedback. It will no longer appear in tracking, queues, or reports. This cannot be undone.</div>
    <dl class="ticket-details">
        <dt>Requester</dt><dd>{{ optional($ticket->employee)->name ?? 'Unavailable requester' }}</dd>
        <dt>Service</dt><dd>{{ optional($ticket->task)->name ?? 'Unavailable service' }}</dd>
        <dt>Status</dt><dd>{{ $ticket->status }}</dd>
        <dt>Submitted</dt><dd>{{ optional($ticket->created_at)->format('M d, Y · h:i A') }}</dd>
    </dl>
    <h2>Concern details</h2>
    <p class="ticket-description">{{ $ticket->support_specifics ?: 'No details provided.' }}</p>
    <form method="post" action="{{ route('dashboard.tickets.destroy', $ticket->id) }}" class="portal-form" data-ticket-delete-confirmation data-confirmation="DELETE #{{ $ticket->id }}">
        @csrf
        @method('DELETE')
        <label for="delete-confirmation">Type <strong>DELETE #{{ $ticket->id }}</strong> to confirm</label>
        <input id="delete-confirmation" name="confirmation" value="{{ old('confirmation') }}" required autocomplete="off" spellcheck="false" aria-describedby="delete-confirmation-hint">
        <p id="delete-confirmation-hint" class="field-hint">The text must match exactly, including the ticket number.</p>
        <label for="delete-password">Your current account password</label>
        <input id="delete-password" type="password" name="password" required autocomplete="current-password">
        <label class="delete-acknowledgement"><input type="checkbox" name="acknowledge" value="1" required @checked(old('acknowledge'))> I understand that this ticket and its feedback will be permanently deleted.</label>
        <div class="dialog-actions">
            <a class="btn btn-secondary" href="{{ route('dashboard') }}">Cancel</a>
            <button type="submit" class="btn btn-danger">Permanently delete ticket #{{ $ticket->id }}</button>
        </div>
    </form>
</section>
@endsection
