@extends('layouts.main')
@section('content')
<!-- Header-->
<header class="py-5">
    <script type="text/javascript">
        //console.log(moment);
        function statusChange(){
            //document.getElementById('selectStatus').value = "Completed";

            //This is to select the string of the option, not the value (which is an integer)
            let select= document.getElementById('selectTechnician');
            let selectedText = select.options[select.selectedIndex].text;
            document.getElementById('technician').value = selectedText;
        }
    </script>
        <div class="bg-light rounded-3 text-center">
            <h2 class="fw-bold">ITSU JOB REQUEST QUEUE - EDIT FORM</h2>
        </div>
    <!-- </div>-->
</header>
<!-- Page Content-->
<section>
    <div class="container">
        <div class="p-4 bg-light rounded-3">
            <form action="/save" method="POST">
                @csrf
                <input id="email" name="email" type="hidden" value="{{ $request[0]->employee->email }}">
                <input id="name" name="name" type="hidden" value="{{ $request[0]->employee->name }}">
                <input id="created_at" name="created_at" type="hidden" value="{{ $request[0]->created_at }}">
                <input id="department" name="department" type="hidden" value="{{ $request[0]->employee->department->name }}">
                <input id="task" name="task" type="hidden" value="{{ $request[0]->task->name }}">
                <input id="specifics" name="specifics" type="hidden" value="{{ $request[0]->support_specifics }}">
                <input id="technician" name="technician" type="hidden" value="{{ $request[0]->technician->name ?? '' }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ID:</label>
                        <input type="text" class="form-control" value="{{ $request[0]->id }}" disabled>
                        <input id="id" name="id" type="hidden" value="{{ $request[0]->id }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Employee:</label>
                        <input type="text" class="form-control" value="{{ $request[0]->employee->name }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Task:</label>
                        <input type="text" class="form-control" value="{{ $request[0]->task->name }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Specifics:</label>
                        <input type="text" class="form-control" value="{{ $request[0]->support_specifics }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Request Date:</label>
                        <input type="text" class="form-control" value="{{ $request[0]->created_at }}" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Accomplish Date:</label>
                        <input id="accDate" name="accDate" type="datetime-local" class="form-control" value="{{ date('Y-m-d H:i:s') }}" step="any">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Technician:</label>
                        <select name="selectTechnician" id="selectTechnician" class="form-select" onchange="statusChange()">
                            @if($request[0]->technician == '')
                                <option value="" selected></option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            @else
                                <option value="" selected></option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ optional($request[0]->technician)->id == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status:</label>
                        <select name="selectStatus" id="selectStatus" class="form-select">
                            <?php $status = array("Pending", "Completed", "Cancelled") ?>
                            @foreach($status as $stat)
                                <option value="{{ $stat }}" {{ $stat === $request[0]->status ? 'selected' : '' }}>{{ $stat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Findings/Actions Taken:</label>
                        <textarea id="findings" name="findings" class="form-control" style="height: 150px;"></textarea>
                    </div>

                    <div class="col-12 text-center">
                        <button class="btn btn-secondary btn-lg me-2" onclick="history.back()" type="button">Back</button>
                        <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection 
