@extends('helpdesk.layouts.dashboard')
@section('title', $type === 'employees' ? 'Delete Pending User' : 'Delete Technician')
@section('content')
<section class="portal-panel ticket-delete-panel">
<h1>@yield('title')</h1><p>Remove <strong>{{ $record->name }}</strong> ({{ $record->email }}) from the directory?</p>
<div class="portal-alert" role="note">@if($type === 'employees')This removes the user from For Activation and the employee directory. The email will remain inactive; contact ITSU if the user needs to register again.@elseThis removes the technician from the directory and new assignment choices. This action does not delete a staff login account.@endif Existing tickets, reports, requester names and technician assignments will be retained in history.</div>
<form method="post" action="{{ route('dashboard.settings.destroy', [$type, $record->id]) }}" class="portal-form" data-directory-delete-confirmation data-confirmation="DELETE #{{ $record->id }}">@csrf @method('DELETE')
<input type="hidden" name="_return_to" value="{{ $returnTo }}">
<label for="directory-confirmation">Type <strong>DELETE #{{ $record->id }}</strong> to confirm</label><input id="directory-confirmation" name="confirmation" value="{{ old('confirmation') }}" required autocomplete="off">
<label for="directory-password">Your current account password</label><input id="directory-password" type="password" name="password" required autocomplete="current-password">
<label class="delete-acknowledgement"><input type="checkbox" name="acknowledge" value="1" required @checked(old('acknowledge'))> I understand this removes the record from the directory and preserves ticket history.</label>
<div class="dialog-actions"><a class="btn btn-secondary" href="{{ $returnTo }}">Cancel</a><button type="submit" class="btn btn-danger">Delete {{ $type === 'employees' ? 'pending user' : 'technician' }}</button></div>
</form></section>
@endsection
