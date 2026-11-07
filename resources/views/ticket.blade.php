@extends('layouts.user')
@section('content')
<!-- Header-->
<header>
    <script type="text/javascript">
        function openPage(pageName, elmnt, color) {
        // Hide all elements with class="tabcontent" by default */
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }

        // Remove the background color of all tablinks/buttons
        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].style.backgroundColor = "";
        }

        // Show the specific tab content
        document.getElementById(pageName).style.display = "block";

        // Add the specific color to the button used to open the tab content
        elmnt.style.backgroundColor = color;
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
    </script>
        
    <!-- </div>-->
</header>
<!-- Page Content-->
<section>
    <!--<div class="container px-lg-5">-->
    <div>
        <!-- Page Features-->
        
        <div class="p-4 p-lg-1 bg-light rounded-3 text-center">
            <h1 class="display-5 fw-bold">TICKET QUEUE</h1>
        </div>
        
        <div id="ticket"></div>
        @vite('resources/js/app.js')
        
    </div>
</section>
@endsection 
