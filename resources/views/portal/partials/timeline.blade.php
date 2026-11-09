<ol class="ticket-timeline">
@forelse($updates->sortBy([['created_at', 'desc'], ['id', 'desc']]) as $update)
<li><span class="timeline-marker"><i class="bi bi-{{ $update->event === 'created' ? 'plus' : 'check2' }}" aria-hidden="true"></i></span><div class="timeline-entry"><div class="timeline-meta"><strong>{{ $update->actor_name }}</strong><time datetime="{{ $update->created_at->toIso8601String() }}">{{ $update->created_at->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</time></div><p>{{ $update->summary }}</p>@if($update->changes)<ul class="timeline-changes">@foreach($update->changes as $label => $change)<li><strong>{{ $label }}:</strong> {{ $change['from'] }} → {{ $change['to'] }}</li>@endforeach</ul>@endif @if($update->action_taken)<div class="timeline-action"><strong>Action Taken</strong><p>{{ $update->action_taken }}</p></div>@endif</div></li>
@empty<li><div class="timeline-entry"><p>No updates recorded yet.</p></div></li>@endforelse
</ol>
