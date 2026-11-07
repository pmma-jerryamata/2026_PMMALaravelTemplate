@extends('layouts.user')
@section('content')

<!-- Header-->
<header class="py-3">
    <div class="container px-lg-5">
        <div class="p-4 p-lg-5 bg-light rounded-3 text-center">

            <form action="{{ route('support') }}" method="GET">
                <h1 class="display-8 fw-bold"><p class="fs-4">Update Successfully Completed.
                    </p></h1>
                <br />
            <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg" type="submit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</button>
            <br />
            <br />
            </form>
        </div>
    </div>
</header>
<!-- Page Content-->
<section class="pt-4">
    <div class="container px-lg-5">
        
    </div>
</section>
@endsection 
