<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ITSU - SURVEY FORM</title>
        @vite('resources/css/styles.css')
        <!-- Favicon-->
        <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{asset('css/sidebar.css')}}" rel="stylesheet" />
        <link href="{{asset('css/styles.css')}}" rel="stylesheet" />
        <style>
        </style>
    </head>
    <body>
    <!-- Responsive navbar-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container px-lg-5">
            <a class="navbar-brand" href="{{ request()->fullUrl() }}"><i class="lni lni-spinner">&nbsp;&nbsp;</i>ITSU - SURVEY FORM</a>
        </div>
    </nav>

    <!-- Header-->
    <header class="py-5">
        <div class="container px-lg-5">
            <div class="p-4 p-lg-5 bg-light rounded-3 text-center">

                    <h1 class="display-8 fw-bold"><p class="fs-4">Thank you for taking the survey.
                        </p></h1>
                    <br />
                    <form action="{{ route('index') }}" method="GET">
                        <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg" type="submit">Done</button>
                    </form>
                </div>
            </div>
        <!-- </div>-->
    </header>
            
    </body>
</html>