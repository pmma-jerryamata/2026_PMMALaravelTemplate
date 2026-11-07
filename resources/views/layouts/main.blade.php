<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ITSU - ADMIN JOB REQUESTS</title>
        @vite('resources/css/app.css')
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/PMMA.ico')}}" />
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{asset('css/styles.css')}}" rel="stylesheet" />
        <style>
            /* Set height of body and the document to 100% to enable "full page tabs" */
            body, html {
            height: 100%;
            margin: 0;
            font-family: Arial;
            }

            /* Style tab links */
            .tablink {
            background-color: #555;
            color: white;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            font-size: 17px;
            width: 25%;
            }

            .tablink:hover {
            background-color: #777;
            }

            /* Style the tab content (and add height:100% for full page content) */
            .tabcontent {
            color: white;
            display: none;
            padding: 100px 20px;
            height: 100%;
            }

            #Home {background-color: red;}
            #News {background-color: green;}
            #Contact {background-color: blue;}
            #About {background-color: orange;}
        </style>
    </head>
    <body>
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-black">
            <div class="container px-lg-5">
                <a class="navbar-brand" href="{{ route('admin.index') }} "><img src="{{ asset('assets/PMMA.gif')}}" class="logo" width="40" height="40">&nbsp;ITSU - VIEW SUPPORTS</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('admin.index') }}">Control Panel</a></li>
                        <li class="nav-item"><a class="nav-link active" href=" {{ route('phone') }}">Phone Directory</a></li>
                        <!--<li class="nav-item"><a class="nav-link active" href="#">Forms</a></li>-->
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Header-->
        @yield('content')

        <!-- Footer-->
        <footer class="py-5 bg-black">
            <p class="m-0 text-center text-white">You are logged-in: {{ Auth::user()->name }}</p>
            <div class="container"><p class="m-0 text-center text-white">&copy; {{ date('Y')}} PMMA - ITSU</p></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js')}}"></script>
    </body>
</html>
