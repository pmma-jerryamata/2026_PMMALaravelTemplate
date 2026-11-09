@php($lastAction = $ticket->latestAction)
@php($lastUpdate = $ticket->latestUpdate)
<div class="ticket-last-action">
    <span>{{ $lastAction ? \Illuminate\Support\Str::limit($lastAction->action_taken, 180) : ($lastUpdate && $lastUpdate->event !== 'created' ? $lastUpdate->summary : 'Awaiting technician action.') }}</span>
    @if($lastAction || $lastUpdate)
        <small>{{ ($lastAction ?? $lastUpdate)->created_at->timezone('Asia/Manila')->format('M d, Y · h:i A') }}</small>
    @endif
</div>
