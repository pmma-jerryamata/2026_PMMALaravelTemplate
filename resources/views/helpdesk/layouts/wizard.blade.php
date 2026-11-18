<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Submit a Ticket | {{ $systemSettings['app_name'] }}</title>
@foreach(['style', 'accessibility', 'pmma', 'legacy-adapter'] as $sheet)<link rel="stylesheet" href="{{ asset('helpdesk/css/'.$sheet.'.css') }}?v={{ $designVersion }}">@endforeach
<link rel="stylesheet" href="{{ asset('helpdesk/icons/bootstrap-icons.css') }}">@stack('page-assets')
<link rel="stylesheet" href="{{ asset('helpdesk/css/wizard-modal.css') }}?v={{ filemtime(public_path('helpdesk/css/wizard-modal.css')) }}">
<script src="{{ asset('helpdesk/js/new-tab-links.js') }}" defer></script>
</head><body class="wizard-modal-page"><main class="wizard-fullscreen" role="dialog" aria-modal="true" aria-labelledby="wizard-title" tabindex="-1"><header class="wizard-modal-header"><div><span>PMMA HELPDESK</span><h1 id="wizard-title">Submit a Ticket</h1></div><a href="{{ route('home') }}" class="wizard-close" aria-label="Close ticket wizard"><i class="bi bi-x-lg" aria-hidden="true"></i></a></header>
@if($errors->any())<div class="portal-alert error wizard-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main></body></html>
