@foreach($services as $slug => $service)
<li class="service-choice-item">
    <a href="{{ route('tickets.create', ['step' => 'issue', 'category' => $slug]) }}" class="service-choice-card" aria-labelledby="service-title-{{ $slug }}" @if($service['description'] !== '') aria-describedby="service-description-{{ $slug }}" @endif>
        <span class="service-choice-icon"><i class="bi bi-{{ $service['icon'] }}" aria-hidden="true"></i></span>
        <div class="service-choice-content">
            <h3 id="service-title-{{ $slug }}">{{ $service['title'] }}</h3>
            @if($service['description'] !== '')<p id="service-description-{{ $slug }}">{{ $service['description'] }}</p>@endif
            <span class="service-choice-action" aria-hidden="true">Select service <i class="bi bi-arrow-right"></i></span>
        </div>
    </a>
</li>
@endforeach
