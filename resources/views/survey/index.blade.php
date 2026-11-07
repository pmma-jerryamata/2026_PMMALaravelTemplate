@extends('helpdesk.layouts.portal')
@section('title', 'Rate Our Service')
@section('content')
@include('helpdesk.portal.partials.heading', ['title' => 'Rate Our Service', 'subtitle' => 'Help us improve ITSU support.'])
<section class="section"><div class="container"><div class="portal-panel adapter-record">@if($support->isNotEmpty())<h2>Ticket #{{ $support[0]->id }}</h2><p>{{ optional($support[0]->task)->name }}</p><form class="portal-form" action="{{ URL::temporarySignedRoute('survey', now()->addDays(7), ['support_id' => $support[0]->id]) }}" method="post">@csrf<fieldset><legend>How satisfied are you with our service?</legend>@foreach($ratings as $rating)<label class="portal-checkbox"><input type="radio" name="satisfactionRating" value="{{ $rating->id }}" required><span>{{ $rating->name }}</span></label>@endforeach</fieldset><label for="feedback">Comments / Suggestions</label><textarea id="feedback" name="feedback" rows="5" maxlength="255"></textarea><button class="btn btn-primary" type="submit">Submit Feedback</button></form>@else<p>Ticket not found.</p>@endif</div></div></section>
@endsection
