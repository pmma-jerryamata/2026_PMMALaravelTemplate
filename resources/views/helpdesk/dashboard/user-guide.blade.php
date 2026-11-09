@extends('helpdesk.layouts.dashboard')
@php
    $language = request('lang') === 'fil' ? 'fil' : 'en';
    $chapters = config('user-guide.chapters');
    $labels = $language === 'fil' ? [
        'title' => 'Gabay sa Paggamit', 'intro' => 'Mga sunod-sunod na tutorial para sa araw-araw na operasyon ng PMMA Helpdesk.',
        'search' => 'Maghanap sa gabay', 'placeholder' => 'Halimbawa: ticket, findings, report, password',
        'contents' => 'Mga Paksa', 'count' => 'paksa', 'of' => 'sa', 'steps' => 'Mga hakbang',
        'note' => 'Tandaan', 'open' => 'Buksan ang screen', 'top' => 'Bumalik sa itaas',
        'print' => 'I-print / I-save bilang PDF', 'empty' => 'Walang tumugmang tutorial. Subukan ang ibang salita o burahin ang search text.',
        'start' => 'Araw-araw na proseso', 'ticket' => 'Gumawa ng ticket', 'report' => 'Gumawa ng report',
    ] : [
        'title' => 'User Guide', 'intro' => 'Step-by-step tutorials for day-to-day PMMA Helpdesk operations.',
        'search' => 'Search the guide', 'placeholder' => 'Try: ticket, findings, report, password',
        'contents' => 'Topics', 'count' => 'topics', 'of' => 'of', 'steps' => 'Step by step',
        'note' => 'Remember', 'open' => 'Open this screen', 'top' => 'Back to top',
        'print' => 'Print / Save as PDF', 'empty' => 'No tutorials match your search. Try another word or clear the search text.',
        'start' => 'Daily workflow', 'ticket' => 'Create a ticket', 'report' => 'Prepare a report',
    ];
@endphp
@section('title', $labels['title'])
@section('language', $language)
@push('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/user-guide.css') }}?v={{ filemtime(public_path('helpdesk/css/user-guide.css')) }}">
<script src="{{ asset('helpdesk/js/user-guide.js') }}?v={{ filemtime(public_path('helpdesk/js/user-guide.js')) }}" defer></script>
@endpush
@section('content')
<div class="user-guide" id="user-guide" data-topic-label="{{ $labels['count'] }}" data-of-label="{{ $labels['of'] }}">
    <section class="guide-hero" aria-labelledby="guide-title">
        <div class="guide-hero-heading">
            <div><span class="portal-eyebrow">PMMA HELPDESK · ENGLISH / FILIPINO</span><h1 id="guide-title">{{ $labels['title'] }}</h1><p>{{ $labels['intro'] }}</p></div>
            <nav class="guide-languages" aria-label="Guide language / Wika ng gabay">
                <a href="{{ route('dashboard.user-guide', ['lang' => 'en']) }}" lang="en" @if($language === 'en') aria-current="page" @endif>English</a>
                <a href="{{ route('dashboard.user-guide', ['lang' => 'fil']) }}" lang="fil" @if($language === 'fil') aria-current="page" @endif>Filipino</a>
            </nav>
        </div>
        <div class="guide-shortcuts"><a href="#guide-getting-started"><i class="bi bi-signpost-split" aria-hidden="true"></i>{{ $labels['start'] }}</a><a href="#guide-create-ticket"><i class="bi bi-plus-circle" aria-hidden="true"></i>{{ $labels['ticket'] }}</a><a href="#guide-reports"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>{{ $labels['report'] }}</a></div>
        <div class="guide-tools" data-guide-tools hidden>
            <div class="guide-search"><label for="guide-search">{{ $labels['search'] }}</label><input id="guide-search" type="search" maxlength="150" placeholder="{{ $labels['placeholder'] }}" aria-controls="guide-chapters" autocomplete="off"></div>
            <button class="btn btn-secondary ui-action" type="button" data-guide-print><i class="bi bi-printer" aria-hidden="true"></i><span>{{ $labels['print'] }}</span></button>
        </div>
        <p class="guide-count" data-guide-count role="status">{{ count($chapters) }} {{ $labels['count'] }}</p>
    </section>
    <div class="guide-layout">
        <aside class="guide-navigation">
            <details class="guide-toc" open><summary>{{ $labels['contents'] }}</summary><nav aria-label="{{ $labels['contents'] }}"><ol>
                @foreach($chapters as $chapter)<li data-guide-nav="{{ $chapter['id'] }}"><a href="#guide-{{ $chapter['id'] }}">{{ $chapter['title'][$language] }}</a></li>@endforeach
            </ol></nav></details>
        </aside>
        <div class="guide-chapters" id="guide-chapters">
            <p class="guide-empty" data-guide-empty hidden>{{ $labels['empty'] }}</p>
            @foreach($chapters as $chapter)
            <article class="portal-panel guide-chapter" id="guide-{{ $chapter['id'] }}" data-guide-chapter="{{ $chapter['id'] }}" aria-labelledby="guide-heading-{{ $chapter['id'] }}">
                <header class="guide-chapter-heading"><span class="guide-chapter-icon"><i class="bi bi-{{ $chapter['icon'] }}" aria-hidden="true"></i></span><div><span class="guide-chapter-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ count($chapters) }}</span><h2 id="guide-heading-{{ $chapter['id'] }}">{{ $chapter['title'][$language] }}</h2></div></header>
                <p class="guide-intro">{{ $chapter['intro'][$language] }}</p>
                <h3 class="sr-only">{{ $labels['steps'] }}</h3>
                <ol class="guide-steps">@foreach($chapter['steps'] as $step)<li>{{ $step[$language] }}</li>@endforeach</ol>
                @if(isset($chapter['note']))<div class="guide-note"><strong><i class="bi bi-info-circle" aria-hidden="true"></i> {{ $labels['note'] }}</strong><p>{{ $chapter['note'][$language] }}</p></div>@endif
                <footer class="guide-chapter-footer">@if(isset($chapter['route']))<a class="guide-open" href="{{ route($chapter['route'], $chapter['parameters'] ?? []).(isset($chapter['fragment']) ? '#'.$chapter['fragment'] : '') }}">{{ $labels['open'] }} <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>@endif<a class="guide-top" href="#user-guide">{{ $labels['top'] }} <i class="bi bi-arrow-up" aria-hidden="true"></i></a></footer>
            </article>
            @endforeach
        </div>
    </div>
</div>
@endsection
