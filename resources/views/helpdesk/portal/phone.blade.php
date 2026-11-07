@extends('helpdesk.layouts.portal')
@section('title', 'Phone Directory')
@section('content')
@include('helpdesk.portal.partials.heading', ['title' => 'PMMA Phone Directory', 'subtitle' => 'Connect with the campus office you need.'])
<section class="section"><div class="container portal-panel"><img class="directory-image" src="{{ asset('assets/phone5.jpg') }}" alt="PMMA Telephone Directory"></div></section>
@endsection
