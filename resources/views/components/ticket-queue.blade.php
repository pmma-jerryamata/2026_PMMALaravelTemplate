@props(['tickets'])

<section {{ $attributes->class('template-queue') }} aria-label="Ticket queue">
    <header class="template-queue-heading">
        <h3>Pending tickets <span>{{ count($tickets) }}</span></h3>
    </header>

    @if(count($tickets))
        <ol class="template-queue-list" role="list">
            @foreach($tickets as $ticket)
                <li>
                    <span class="template-queue-position" aria-label="Position {{ $loop->iteration }} in queue">{{ $loop->iteration }}</span>
                    <div>
                        <h4>Ticket #{{ $ticket['reference'] }}</h4>
                        <p>{{ $ticket['service'] }}</p>
                        <dl>
                            <div><dt>Submitted</dt><dd>{{ $ticket['submitted'] }}</dd></div>
                            <div><dt>Last Updated</dt><dd>{{ $ticket['updated'] }}</dd></div>
                        </dl>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <p class="template-queue-empty">No pending tickets.</p>
    @endif
</section>
