<section class="government-footer" aria-labelledby="government-footer-heading">
    <h2 class="sr-only" id="government-footer-heading">Philippine Government Links</h2>
    <div class="container government-footer-grid">
        <div class="government-emblem"><a href="https://www.gov.ph/" aria-label="Republic of the Philippines government portal"><img src="{{ asset('helpdesk/images/republic-of-the-philippines.png') }}" alt="Coat of arms of the Republic of the Philippines" width="112" height="124" loading="lazy"></a></div>
        @foreach(config('footer.government_columns') as $column)
        <div class="government-link-column">
            @foreach($column as $group)
            <nav class="government-link-group" aria-label="{{ $group['title'] }}">
                <h3>{{ $group['title'] }}</h3>
                <ul>@foreach($group['links'] as $link)<li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>@endforeach</ul>
            </nav>
            @endforeach
        </div>
        @endforeach
    </div>
</section>
