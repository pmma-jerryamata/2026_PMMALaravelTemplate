<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ITSU - ADMINISTRATOR'S PAGE</title>
        @vite('resources/css/app.css')
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/PMMA.ico') }}" />
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
        <nav class="navbar navbar-expand-lg navbar-dark bg-black">
            <div class="container px-lg-5">
                <img src="{{ asset('assets/PMMA.gif')}}" class="logo" width="80" height="80">
                <a class="navbar-brand" href="{{ route('admin.index') }} ">&nbsp;PMMA-ITSU ADMIN<h6>&nbsp;&nbsp;@auth{{ Auth::user()->email }}@endauth</h6></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('support') }}">Support</a></li>
                        <li class="nav-item"><a class="nav-link active" href=" {{ route('phone') }}">Phone Directory</a></li>
                        <li class="nav-item"><a class="nav-link active" href="#">Forms</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Header-->
        @yield('content')

        <!-- Footer-->
        <footer class="py-5 bg-black">
            <div class="container"><p class="m-0 text-center text-white">&copy; {{ date('Y')}} PMMA - ITSU</p></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js')}}"></script>
    </body>
</html>
