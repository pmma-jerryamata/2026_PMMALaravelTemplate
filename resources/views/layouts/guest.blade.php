@extends('helpdesk.layouts.auth')
@section('content')
<section class="section login-section"><div class="container"><div class="login-panel portal-panel legacy-component"><a href="{{ route('home') }}"><img src="{{ asset('helpdesk/images/pmma-seal.png') }}" alt="PMMA seal" width="72" height="72"></a>{{ $slot }}</div></div></section>
@endsection