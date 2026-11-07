@extends('layouts.main')
@section('content')

<!-- Header-->
<header class="py-3">
    <script type="text/javascript">
        function loadEmployees(dept, arrEmployee){
            //alert(dept);
            document.getElementById("selectEmployee").removeAttribute("disabled");
            document.getElementById('selectEmployee').options.length = 0;
            var selectElement = document.getElementById('selectEmployee');
            var opt = document.createElement("option");
                    //opt.value = null;
                    //opt.text = "--SELECT--";
                    //selectElement.add(opt, null);
            for (i = 0; i < arrEmployee.length; i++){
                if(dept == arrEmployee[i]['department_id']){
                    opt = document.createElement("option");
                    opt.value = arrEmployee[i]['id'] + "," + arrEmployee[i]['email'];
                    opt.text = arrEmployee[i]['name'] + " - (" + arrEmployee[i]['email'] + ")";
                    selectElement.add(opt, null);
                } 
            }
        }
        function chooseEmployee(emp){
            document.getElementById("empId").value = emp;

        }
    </script>
    <div class="container px-lg-5">
        <div class="p-4 p-lg-1 bg-light rounded-3 text-center">
            <h1 class="display-5 fw-bold">ITSU JOB REQUEST</h1>
            <br />
            <!--Start / Pop Errors for Validation of Fields-->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            @if($error == "The select task must be an integer.")
                                <li>The select task is required.</li>
                            @else
                                <li>{{ $error }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
            <!--End / Pop Errors for Validation of Fields-->

            <form action="{{ route('support-admin') }}" method="POST">
            @csrf
            <table align='center'>
                <tr>
                    <td><h4>Department/Unit :&nbsp;&nbsp;</h4></td>
                    <td align="left"><h4><select name="selectDepartment" id="selectDepartment" sytle="width:120px" onchange="loadEmployees(this.value, {{ $employees }})">   
                        <option disabled selected value style="display:none;">--SELECT--</option> 
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select></h4>
                    </td>
                </tr>
                <tr>
                    <td><h4>Employee Name :&nbsp;&nbsp;</h4></td>
                    <td align="left"><h4><select name="selectEmployee" id="selectEmployee" style="width:100%" onchange="chooseEmployee(this.value)" disabled>
                            <option default>--SELECT--</option>
                        </select></h4>
                    </td>
                </tr>
                <tr>
                    <td align="right"><h4>Task :&nbsp;&nbsp;</h4></td>
                    <td align="left"><h4><select name="selectTask" id="selectTask" style="width:100%">
                            <option disabled selected value style="display:none;">--SELECT--</option>
                            @foreach ($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->name }}</option>
                        @endforeach 
                        </select></h4>
                    </td>
                </tr>
                <tr>
                    <td align="right"><h4>Specifics :&nbsp;&nbsp;</h4></td>
                    <td align="left">
                        <textarea name="specificsField" id="specificsField" style="width:100%; height:10vh" placeholder="If identifiable, please provide brief specifics of the problem encountered."></textarea>
                    </td>
                </tr>
                <tr>
                    <td align="right"><h4>Technician :&nbsp;&nbsp;</h4></td>
                    <td align="left"><h4><select name="selectTechnician" id="selectTechnician" style="width:100%">
                        <option value=""></option>
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                        @endforeach 
                        </select></h4>
                    </td>
                </tr>
            </table>
            <br />
            <input name="empId" id="empId" value="" hidden/>
            <button class="btn btn-primary btn-lg" onclick="history.back()" type="button">&nbsp;&nbsp;Back&nbsp;&nbsp;</button>
            <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg" type="submit">Submit</button>              
            <br />
            <br />
            </form>
        </div>
    </div>
</header>
