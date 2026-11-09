<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ITSU - SUPPORT TEAM</title>
        @vite('resources/css/app.css')
        <!-- Favicon-->
        <link rel="icon" type="image/png" href="{{ asset('helpdesk/images/pmma-logo.png')}}" />
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
        @auth
            <input id='username' name='username' type='hidden' value= '{{ Auth::user()->name }}' >
            <input id='userId' name='userId' type='hidden' value= '{{ Auth::user()->id }}' >
        @endauth
        <!-- Responsive navbar-->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container px-lg-5">
                <a class="navbar-brand" href="{{ route('tech.index') }} "><img src="{{ asset('helpdesk/images/pmma-logo.png')}}" class="logo" width="40" height="40">&nbsp;TECH PAGE</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" aria-current="page" onclick="loadBody('ticket')"><i class="lni lni-stackoverflow">&nbsp;</i>ACTIVE TICKETS</a></li>
                        <li class="nav-item"><a class="nav-link active" aria-current="page" onclick="loadBody('create')"><i class="lni lni-add-files">&nbsp;</i>ADD REQUEST</a></li>
                        <!--<li class="nav-item"><a class="nav-link active" aria-current="page" onclick="loadBody('completed')"><i class="lni lni-slack">&nbsp;</i>COMPLETED</a></li>-->
                    </ul>
                </div>
            </div>
        </nav>

        <header>
            <div class="container px-lg-5">
                <div class="p-1 p-lg-1 bg-light rounded-3 text-center">
                    <h1 class="display-5 fw-bold" id='title' name='title'>TICKET QUEUE</h1>
                </div>
            </div>
            <script type="text/javascript">
                function loadBody(button) {
                    if(button == "create"){
                        document.getElementById('loadTicket').style.display = "none";
                        document.getElementById('loadCompleted').style.display = "none";
                        document.getElementById('loadCreate').style.display = "block";
                        document.getElementById('title').innerHTML = "CREATE TICKET";
                    }else if(button == "ticket"){
                        window.location.reload();
                        document.getElementById('loadCreate').style.display = "none";
                        document.getElementById('loadCompleted').style.display = "none";
                        document.getElementById('loadTicket').style.display = "block";
                        document.getElementById('title').innerHTML = "TICKET QUEUE";
                    }else if(button == "completed"){
                        document.getElementById('loadCreate').style.display = "none";
                        document.getElementById('loadTicket').style.display = "none";
                        document.getElementById('loadCompleted').style.display = "block";
                        document.getElementById('title').innerHTML = "COMPLETED";
                    }
                }
            </script>
        </header>

        <section class="pt-4">
        <div>
            <div class="text-center" id="loadTicket" name="loadTicket" style="display:block">
                <h1><div id='techticket'></div></h1>
            </div>
            <div class="text-center" id="loadCreate" name="loadCreate" style="display:none">
                <h1><div id='techadd' tech-id={{ (int)Auth::user()->id }}>

                </div></h1>
            </div>
            <div class="text-center" id="loadCompleted" name="loadCompleted" style="display:none">
                <h1><div id='techcompleted'></div></h1>
            </div>
            @vite('resources/js/apptech.js')
        </div>
        </section>
        <!-- Footer-->
        <footer class="py-5 bg-dark">
            <div class="container">
                <p class="m-0 text-center text-white">You are logged-in: {{ Auth::user()->name }}</p>
                <p class="m-0 text-center text-white">&copy; {{ date('Y')}} PMMA - ITSU</p>
            <div>
        
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js')}}"></script>
    </body>
</html>
