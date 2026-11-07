@extends('layouts.user')
@section('content')

<!-- Header-->
<header class="py-3">
    <script type="text/javascript">
        function putRemEmail() {
            //alert("Pumasok sa putRemEmail.");
            document.getElementById("email").value = localStorage.getItem("empEmail");
        }
        function rememberMe() {
            localStorage.setItem("empEmail", document.getElementById("email").value);
            document.getElementById("email").value = localStorage.getItem("empEmail");
        }
        function setEmail(arr){
            
            document.getElementById('emailProxy').value = document.getElementById('email').value;
            //alert("Set Email"+document.getElementById('email').value);
        }
        function checkEmail(){
            document.getElementById('error').innerHTML = "";
            if(document.getElementById('email').value == ""){
                document.getElementById("emailDiv").scrollIntoView();
                document.getElementById("email").focus();
                alert("Please input your PMMA e-mail account!");
            }
        }
        function setSubmit(data){
            //alert("Data: "+data);
            document.getElementById('emailProxy').value = document.getElementById('email').value;
            document.getElementById('tasks1').value = data;
            $stringBtn = "submit"+data;
            for(i=1; i<7; i++){
                if(i == data){
                    document.getElementById($stringBtn).removeAttribute("hidden");
                    document.getElementById($stringBtn).focus();
                }else{
                    $stringB = "submit"+i;
                    document.getElementById($stringB).setAttribute("hidden","hidden");
                }
            }
            
            //alert("Set Email"+document.getElementById('email').value);
        }
        function setHidden(){
            for(i=1; i<7; i++){
                $stringB = "submit"+i;
                document.getElementById($stringB).setAttribute("hidden","hidden");
            }
            
            //alert("Set Email"+document.getElementById('email').value);
        }
        function checkUser(){
            //alert("Check User");
        }
    </script>
    <div id="emailDiv" name="emailDiv" class="container px-lg-5">
        <div class="p-4 p-lg-1 bg-light rounded-3 text-center">
            <h1 class="display-5 fw-bold">JOB REQUEST PORTAL</h1>
            <p class="fs-4">Input your E-mail : <input name="email" id="email" type="email" placeholder="user.lastname@pmma.edu.ph" onfocus="setHidden()" autocomplete="email" class="rounded-3 shadow-sm" /></p>
        </div>
        <h2 id="error" name="error" align="center" style="background-color: #ff0000">{{ $error }}</h2>      
    </div>
    
    <!-- </div>-->
</header>
<!-- Page Content-->
<section class="pt-4">
    <div class="container px-lg-5">
        <!-- Page Features-->
        <form action="{{ route('info') }}" method="GET">
        @csrf
        <div class="row gx-lg-5">
            <?php $ctr=1; ?>

            @foreach ($tasks as $task)
            <div class="col-lg-6 col-xxl-4 mb-5">
                
                <div class="card bg-light border-0 h-100">
                    <div class="card-body text-center p-4 p-lg-3 pt-0 pt-lg-0" style="border:1px solid black" onclick="setSubmit(<?php echo $ctr; ?>)">
                        <div class="feature bg-primary bg-gradient text-white rounded-3 mb-4 mt-n4"><i class="{{ $task->icon }}"></i></div>
                        <h2 class="fs-4 fw-bold">{{ $task->name }}</h2>
                        <p class="mb-0">{{ $task->content }}</p>
                        <br />
                        <button class="btn btn-primary btn-lg" id="submit<?php echo $ctr; ?>" name="submit<?php echo $ctr++; ?>" type="submit" onmouseover="rememberMe(); setEmail(); checkEmail();" hidden>Submit Request</button>
                    </div>
                </div>
            </div>
            @endforeach
            <input id="emailProxy" name="emailProxy" value="" type="hidden" />
            <input id="tasks1" name="tasks1" value="" type="hidden" />
        </div>
        </form>
    </div>
</section>
@endsection 
