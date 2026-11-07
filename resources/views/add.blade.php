@extends('layouts.main')
@section('content')

<!-- Header-->
<header class="py-3">
    <script type="text/javascript">
        function loadEmployees(dept, arrEmployee) {
            document.getElementById("selectEmployee").removeAttribute("disabled");
            document.getElementById('selectEmployee').options.length = 0;
            var selectElement = document.getElementById('selectEmployee');
            var opt = document.createElement("option");
            for (i = 0; i < arrEmployee.length; i++) {
                if (dept == arrEmployee[i]['department_id']) {
                    opt = document.createElement("option");
                    opt.value = arrEmployee[i]['id'] + "," + arrEmployee[i]['email'];
                    opt.text = arrEmployee[i]['name'] + " - (" + arrEmployee[i]['email'] + ")";
                    selectElement.add(opt, null);
                }
            }
        }

        function chooseEmployee(emp) {
            document.getElementById("empId").value = emp;
        }
    </script>

    <div class="container">
        <div class="p-4 bg-light rounded-3">
            <h1 class="display-5 fw-bold text-center">ITSU JOB REQUEST</h1>
            <br />

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error == "The select task must be an integer." ? "The select task is required." : $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('support-admin') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6 text-start">
                        <label class="form-label">Department/Unit:</label>
                        <select name="selectDepartment" id="selectDepartment" class="form-select" onchange="loadEmployees(this.value, {{ $employees }})">
                            <option disabled selected value style="display:none;">--SELECT--</option>
                            @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 text-start">
                        <label class="form-label">Employee Name:</label>
                        <select name="selectEmployee" id="selectEmployee" class="form-select" onchange="chooseEmployee(this.value)" disabled>
                            <option default>--SELECT--</option>
                        </select>
                    </div>

                    <div class="col-md-6 text-start">
                        <label class="form-label">Task:</label>
                        <select name="selectTask" id="selectTask" class="form-select">
                            <option disabled selected value style="display:none;">--SELECT--</option>
                            @foreach ($tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 text-start">
                        <label class="form-label">Technician:</label>
                        <select name="selectTechnician" id="selectTechnician" class="form-select">
                            <option value=""></option>
                            @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}">{{ $technician->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 text-start">
                        <label class="form-label">Specifics:</label>
                        <textarea name="specificsField" id="specificsField" class="form-control" style="height: 10vh" placeholder="Provide brief specifics of the problem encountered."></textarea>
                    </div>

                    <input name="empId" id="empId" value="" hidden />

                    <div class="col-12 text-center">
                        <button class="btn btn-secondary btn-lg me-2" onclick="history.back()" type="button">&nbsp;&nbsp;Back&nbsp;&nbsp;</button>
                        <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</header>
@endsection