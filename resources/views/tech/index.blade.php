@extends('layouts.maintech')
@section('content')
<!-- Header-->
<header class="py-3">
    <div class="container px-lg-5">
        <div class="p-1 p-lg-1 bg-light rounded-3 text-center">
            <h1 class="display-5 fw-bold">TITLE TITLE TITLE</h1>
        </div>
    </div>
</header>
<!-- Page Content-->
<section class="pt-4">
    <div>
        <div class="text-center" id="loadTicket" name="loadTicket" style="display:none">
            This is Ticket
            <h1><div id='techticket'></div></h1>
        </div>
        <div class="text-center" id="loadCreate" name="loadCreate" style="display:none">
            This is Add
            <h1><div id='techadd'></div></h1>
        </div>
        @vite('resources/js/app.js')
    </div>
</section>
@endsection 
