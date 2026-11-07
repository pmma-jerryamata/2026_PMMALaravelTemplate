@extends('layouts.user')
@section('content')

<!-- Header-->
<header class="py-5">
    <div class="container px-lg-5">
        <div class="p-4 p-lg-5 bg-light rounded-3 text-center">

            <form method="GET" action="{{ route('ticket') }}" >
                <h1 class="display-8 fw-bold"><p class="fs-4">Your request has been queued.
                    <br /><br />Ticket No.: {{ $ticket }}
                    <br /><br />Our team will send support the soonest possible time.
                    </p></h1>
                <br />
                <button class="btn btn-primary btn-lg">Done</button>

            </form>
            </div>
        </div>
    <!-- </div>-->
</header>
        
@endsection