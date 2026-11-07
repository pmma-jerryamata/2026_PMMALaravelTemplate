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
    
    <div class="container px-3">
        <div class="p-4 bg-light rounded-3 text-center">
            <form action="{{ route('notify') }}" method="GET">
                @csrf
                <!-- Save token to avoid duplication of entry due to refresh -->
                <input type="hidden" name="submission_token" value="{{ Str::uuid() }}">
                <!--
                <h1 class="fs-4 fw-bold text-center">Please provide additional information:</h1>
                <hr />
                -->
                <div class="d-none">
                    <h3 class="fs-5">Name: {{ $employee->name }} ({{ $employee->email }})</h3>
                    <hr />
                    <label class="fs-6 fw-bold">Department/Unit:</label>
                    <select name="selectDepartment" id="selectDepartment" class="form-select w-100">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $dept->id == $employee->department->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    <hr />
                </div>
    
                <label class="fs-5 fw-bold d-block text-start">You need assistance on: <span class="fw-normal">{{ $userTask['name'] }}</span></label>
                <hr />
    
                <label for="specificsField" class="fs-6 fw-bold text-start d-block">**If possible, briefly describe the problem:</label>
                <textarea name="specificsField" id="specificsField" class="form-control w-100" rows="4" placeholder="Example: Trouble signing in; Cannot access the Document Tracking System (DTS); The phone line is down; Unable to access Outlook; etc."></textarea>
    
                <input name="task" id="task" value="{{ $mytasks->tasks1 }}" type="hidden" />
                <input name="empId" id="empId" value="{{ $employee->id }}" type="hidden" />
    
                <hr />
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-secondary px-4" onclick="history.back()" type="button">&nbsp;&nbsp;Back&nbsp;&nbsp;</button>
                    <button id="submitBtn" name="submitBtn" class="btn btn-primary px-4" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</header>
        
@endsection
