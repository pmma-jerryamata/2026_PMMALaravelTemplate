<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Ticket Queue | {{ $systemSettings['app_name'] }}</title>
    <link rel="icon" href="{{ asset('helpdesk/images/pmma-seal.png') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('helpdesk/css/queue.css') }}?v={{ filemtime(public_path('helpdesk/css/queue.css')) }}">
    <script src="{{ asset('helpdesk/js/legacy-adapter.js') }}?v={{ $designVersion }}" defer></script>
</head>
<body class="helpdesk-queue">
    <header class="queue-topbar">
        <div class="queue-header-row">
            <a class="queue-brand" href="{{ route('home') }}">
                <img src="{{ asset('helpdesk/images/pmma-seal.png') }}" alt="PMMA seal" width="56" height="56">
                <span><strong>{{ $systemSettings['app_name'] }}</strong><small>PMMA SUPPORT OPERATIONS</small></span>
            </a>
            <div class="queue-clock" aria-label="Philippine time"><strong id="queue-time">--:--</strong><small id="queue-date">PHT</small></div>
        </div>
    </header>
    <main class="queue-main" data-queue-refresh>
        <section class="queue-page-heading" aria-labelledby="queue-title">
            <div><span class="queue-eyebrow">SUPPORT OPERATIONS</span><h1 id="queue-title">Ticket Queue</h1><p>Latest 90 requests submitted in the last 7 days.</p></div>
            <div class="queue-heading-actions"><span class="queue-refresh-note"><span aria-hidden="true"></span>Refreshes every 30 seconds</span><a class="queue-back" href="{{ request()->routeIs('dashboard.queue') ? route('dashboard') : route('home') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ request()->routeIs('dashboard.queue') ? 'Back to Dashboard' : 'Back to Portal' }}</a></div>
        </section>
        <div class="queue-columns">
            @foreach(['Pending' => 'hourglass-split', 'Completed' => 'check2-circle', 'Cancelled' => 'x-circle'] as $status => $icon)
            <section class="queue-column" data-status="{{ $status }}" aria-labelledby="queue-{{ strtolower($status) }}">
                <header class="queue-column-heading"><h2 id="queue-{{ strtolower($status) }}"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i>{{ $status }}</h2><span class="queue-count" aria-label="{{ $groups->get($status, collect())->count() }} {{ strtolower($status) }} tickets">{{ $groups->get($status, collect())->count() }}</span></header>
                <div class="queue-ticket-list" tabindex="0" aria-label="{{ $status }} tickets">
                    @forelse($groups->get($status, collect()) as $ticket)
                    <article class="queue-ticket">
                        <div class="queue-ticket-heading"><strong>Ticket #{{ $ticket->id }}</strong><span class="queue-status">{{ $status }}</span></div>
                        <h3>{{ optional($ticket->task)->name ?? 'Support request' }}</h3>
                        <p class="queue-department"><i class="bi bi-building" aria-hidden="true"></i><span>{{ optional(optional($ticket->employee)->department)->name ?? 'Not specified' }}</span></p>
                        <div class="queue-ticket-footer"><span>Last updated</span><time datetime="{{ optional($ticket->updated_at)->toIso8601String() }}">{{ optional($ticket->updated_at)->format('M d, h:i A') ?? 'Not recorded' }}</time></div>
                    </article>
                    @empty
                    <div class="queue-empty"><i class="bi bi-{{ $icon }}" aria-hidden="true"></i><h3>No {{ strtolower($status) }} tickets</h3><p>No matching requests in this period.</p></div>
                    @endforelse
                </div>
            </section>
            @endforeach
        </div>
        <p class="queue-bottom-note">Philippine Merchant Marine Academy · Information Technology Services Unit</p>
    </main>
</body>
</html>