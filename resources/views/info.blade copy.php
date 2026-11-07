@extends('layouts.user')
@section('content')

<!-- Header-->
<header class="py-5">
    <script type="text/javascript">
        function funct(arr)
        {
            alert("JScript : " + arr[0]['email']);
            for (i = 0; i < arr.length; i++){
                alert(arr[i]['email']);
            }
                
            
        }
        function popDepartments(arrDepartment)
        {
            //alert("Inside Department.");      
            document.getElementById("selectOfficeText").innerHTML = "Select your Office/Unit";
            document.getElementById("specificsText").innerHTML = "If identifiable, please provide brief specifics of the problem encountered :";
            document.getElementById("specificsField").removeAttribute("hidden");
            document.getElementById("submitBtn").removeAttribute("hidden");
            document.getElementById("selectDepartment").removeAttribute("hidden");
            document.getElementById("onClickNext").setAttribute("hidden","hidden");
        }
    </script>
    
    <div class="container px-lg-5">
        <div class="p-4 p-lg-1 bg-light rounded-3 text-center">
            
            <form action="{{ route('notify') }}" method="GET"> <!-- {{ route('notify') }} -->
                @csrf
                <!--Save token to avoid duplication of entry because of refresh-->
                <input type="hidden" name="submission_token" value="{{ Str::uuid() }}">

                <!-- return view('login',['mytasks'=>$request,'departments' => $allDepartments, 'employee' => $employees]);-->
                <h1 class="display-12 fw-bold">Please provide additional information&nbsp;:</h1>
                <hr />
                <div style="display:none">
                <h3>
                    Name : {{ $employee->name }} ({{ $employee->email }})
                </h3>
                <hr />
                <p class="fs-4">Department/Unit : <select name="selectDepartment" id="selectDepartment" sytle="text-align-last:center" >
                    
                @foreach ($departments as $dept)
                    @if($dept->id != $employee->department->id)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @else
                        <option value="{{ $dept->id }}" selected="selected">{{ $dept->name }}</option>
                    @endif
                @endforeach
                </select></p>
                <hr />
                </div>
                <label class="fs-4">You need assistance on : {{ $userTask['name'] }} </label>
                <hr />
                <label for="specifics" id="specificsText">**If identifiable, please provide brief specifics of the problem encountered :</label>
                <textarea name="specificsField" id="specificsField" style="width:85%; height:15vh" placeholder="Example: Trouble signing in; Cannot access the Document Tracking System (DTS); The phone line is down; Unable to access Outlook; etc." value=""></textarea>
                <input name="task" id="task" value="{{ $mytasks->tasks1 }}" hidden/>
                <input name="empId" id="empId" value="{{ $employee->id }}" hidden/>
                <!--<input name="date" id="date" value="{{ date('Y-m-d H:i:s') }}" hidden/>-->
                <hr />
                <button class="btn btn-primary btn-lg" onclick="history.back()" type="button">&nbsp;&nbsp;Back&nbsp;&nbsp;</button>
                <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg" type="submit">Submit</button>              
                
            
            </form>
            <br>
            </div>
    </div>
</header>
        
@endsection
