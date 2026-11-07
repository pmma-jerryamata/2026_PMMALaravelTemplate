<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <!--progress bar-->
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ITSU - JOB REQUESTS PORTAL</title>
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

            /* Basic table styling */
            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                padding: 8px;
                text-align: left;
                border: 1px solid #ddd;
            }

            /* For small screens (mobile view) */
            @media screen and (max-width: 600px) {
                .mobile-bg {
                    background-color: #008CBA !important; /* Apply white background only on mobile */
                }
                .mobile-hide {
                    display: none !important; /* Apply white background only on mobile */
                }
                .mobile-show {
                    display: inline !important; /* Apply white background only on mobile */
                }
                table {
                border: none;  /* Remove table border */
                }

                thead {
                display: none;  /* Hide table headers */
                }

                tr {
                display: block; /* Make rows block-level */
                margin-bottom: 10px; /* Add space between rows */
                padding: 4px;
                background-color: #008CBA; /* Box background */
                border: 1px solid #ddd; /* Box border */
                border-radius: 5px; /* Rounded corners */
                }

                td {
                display: block; /* Make each cell block-level */
                width: 100%;
                padding-left: 50%; /* Create space for data label */
                margin-bottom: 1px;
                }

                td:before {
                content: attr(data-label);
                position: absolute;
                top: 10px;
                left: 10px;
                font-weight: bold;
                color: #333;
                }
            }
        </style>
    </head>
    <!-- <body onload="putRemEmail()">
        Responsive navbar-->
    <body>  
        <!-- This will alert after taking the survey -->  
        @if (session('alert'))
            <script>
                alert("{{ session('alert') }}");
            </script>
        @endif
        <nav class="navbar navbar-expand-lg navbar-dark bg-black">
            <div class="container px-lg-5">
                <a class="navbar-brand" href="{{ route('index') }} "><img src="{{ asset('assets/PMMA.gif')}}" class="logo" width="80" height="80">&nbsp;&nbsp;ITSU HELP DESK</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="{{ route('index') }}">Support</a></li>
                        <li class="nav-item"><a class="nav-link active" href=" {{ route('ticket') }} ">Ticket Queue</a></li>
                        <li class="nav-item"><a class="nav-link active" href=" {{ route('phone') }} ">Phone Directory</a></li>
                        <!--<li class="nav-item"><a class="nav-link active" href="#">Forms</a></li>-->
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
