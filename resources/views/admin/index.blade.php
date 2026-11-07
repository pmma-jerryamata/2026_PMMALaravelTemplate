@extends('layouts.admin')
@section('content')
<!-- Header-->
<header>
    <script type="text/javascript">
        function loadBody(button) {
            //alert(button);
            if(button == "create"){
                document.getElementById('loadUpdate').style.display = "none";
                document.getElementById('loadRemove').style.display = "none";
                document.getElementById('loadCreate').style.display = "block";
            }else if(button == "update"){
                document.getElementById('loadCreate').style.display = "none";
                document.getElementById('loadRemove').style.display = "none";
                document.getElementById('loadUpdate').style.display = "block";
            }else if(button == "remove"){
                document.getElementById('loadUpdate').style.display = "none";
                document.getElementById('loadCreate').style.display = "none";
                document.getElementById('loadRemove').style.display = "block";
            }
        } 
        function handleButtonClick(button) {
            const sections = {
                create: 'loadCreate',
                update: 'loadUpdate',
                remove: 'loadRemove',
                survey: 'loadSurvey',
            };

            // Hide all sections
            Object.values(sections).forEach(section => {
                document.getElementById(section).style.display = 'none';
            });

            // Show the selected section
            if (sections[button]) {
                document.getElementById(sections[button]).style.display = 'block';
            }
        }
    </script>
</header>
<!-- Page Content-->
<section>
        <div class="wrapper">
            <aside id="sidebar" class="bg-black">
                <div class="d-flex bg-black">
                    <button class="toggle-btn" type="button">
                        <i class="lni lni-grid-alt"></i>
                    </button>
                    <div class="sidebar-logo">
                        <a href="#">Admin Panel</a>
                    </div>
                </div>
                <ul class="sidebar-nav bg-black">
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                            data-bs-target="#emp" aria-expanded="false" aria-controls="emp">
                            <i class="lni lni-user"></i>
                            <span>Employee</span>
                        </a>
                        <ul id="emp" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link" onclick="handleButtonClick('create')">Create</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link" onclick="handleButtonClick('update')">Update</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link" onclick="handleButtonClick('remove')">Delete</a>
                            </li>
                        </ul>
                    </li>
                    <!-------------------------------Survey-------------------------------------->
                    <div class="sidebar-footer">
                        <a href="#" class="sidebar-link" onclick="handleButtonClick('survey')">
                            <i class="lni lni-pencil-alt"></i>
                            <span>Survey Result</span>
                        </a>
                    </div>
                    <!-------------------------------Reports------------------------------------->
                    <div class="sidebar-footer">
                        <a href="/report" class="sidebar-link" onclick="">
                            <i class="lni lni-calendar"></i>
                            <span>Reports</span>
                        </a>
                    </div>
                    <!-----------------------------Auth-----------------------------------------
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                            data-bs-target="#auth" aria-expanded="false" aria-controls="auth">
                            <i class="lni lni-protection"></i>
                            <span>Auth</span>
                        </a>
                        <ul id="auth" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Login</a>
                            </li>
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link">Register</a>
                            </li>
                        </ul>
                    </li>->
                    
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                            data-bs-target="#multi" aria-expanded="false" aria-controls="multi">
                            <i class="lni lni-layout"></i>
                            <span>Multi Level</span>
                        </a>
                        <ul id="multi" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                            <li class="sidebar-item">
                                <a href="#" class="sidebar-link collapsed" data-bs-toggle="collapse"
                                    data-bs-target="#multi-two" aria-expanded="false" aria-controls="multi-two">
                                    Two Links
                                </a>
                                <ul id="multi-two" class="sidebar-dropdown list-unstyled collapse">
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 1</a>
                                    </li>
                                    <li class="sidebar-item">
                                        <a href="#" class="sidebar-link">Link 2</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">
                            <i class="lni lni-popup"></i>
                            <span>Notification</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="#" class="sidebar-link">
                            <i class="lni lni-cog"></i>
                            <span>Setting</span>
                        </a>
                    </li>
                </ul>-->
                    <div class="sidebar-footer">
                        
                        <a href="#" class="sidebar-link">
                            <form method="POST" action="{{ route('logout') }}">
                            <i class="lni lni-exit" style="text-decoration: none;"></i>
                            @csrf <!-- Protects against CSRF attacks -->
                                <span><button type="submit" style="background: none; border: none; padding: 0; margin: 0; font: inherit; color: inherit; cursor: pointer; text-decoration: underline;">
                                    Logout
                                </button></span>
                            </form>
                        </a>
                        
                    </div>
                </ul>
            </aside>
            <div class="main p-3">
                <div class="text-center" id="loadCreate" name="loadCreate" style="display:none">
                    <!-- This is Create -->
                    <h1><div id='create'></div></h1>
                </div>
                <div class="text-center" id="loadUpdate" name="loadUpdate" style="display:none">
                    <!-- This is Update -->
                    <h1><div id='update'></div></h1>
                </div>
                <div class="text-center" id="loadRemove" name="loadRemove" style="display:none">
                    <!-- This is Remove/Delete -->
                    <h1><div id='remove'></div></h1>
                </div>
                <div class="text-center" id="loadSurvey" name="loadSurvey" style="display:none">
                    <!-- This is Survey -->
                    <h1><div id='survey'></div></h1>
                </div>
                @vite('resources/js/app.js')
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
            crossorigin="anonymous"></script>
        <!--<script src="script.js"></script>-->
</section>
@endsection 