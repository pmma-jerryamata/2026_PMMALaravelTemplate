<div class="container registrar-government-footer">
    <a target="_blank" rel="noopener" class="registrar-government-emblem" href="https://www.gov.ph/" aria-label="Republic of the Philippines government portal">
        <img class="registrar-footer-seal" src="{{ asset('assets/images/republic-of-the-philippines.png') }}" alt="Coat of arms of the Republic of the Philippines" width="155" height="171" loading="lazy">
    </a>

    @foreach (config('registrar.government_columns') as $column)
        <div>
            @foreach ($column as $group)
                <nav class="registrar-government-links" aria-label="{{ $group['title'] }}">
                    <h2>{{ $group['title'] }}</h2>
                    <ul>
                        @foreach ($group['links'] as $link)
                            <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
        </div>
    @endforeach
</div>
