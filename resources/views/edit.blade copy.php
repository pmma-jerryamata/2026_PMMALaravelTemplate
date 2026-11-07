@extends('layouts.main')
@section('content')
<!-- Header-->
<header class="py-5">
    <script type="text/javascript">
        //console.log(moment);
        function statusChange(){
            document.getElementById('selectStatus').value = "Completed";

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
    <!--<div class="container px-lg-5">-->
    <div>
        <!-- Page Features
        
        <div id="edit"></div>-->
        
        {{ $request }}
        {{ optional($request[0]->technician)->id }}
        <!--new MailerStatus($_ticketNumber, $_status, $_dateRequest, $_name, $_department, $_task, $_specifics, $_findings, $_technician, $_dateAccomplished)-->
        <form action= "/save"  method="POST">
        @csrf
        <input id="email" name="email" type="hidden" value= "{{ $request[0]->employee->email }}" >
        <input id="name" name="name" type="hidden" value= "{{ $request[0]->employee->name }}" >
        <input id="created_at" name="created_at" type="hidden" value= "{{ $request[0]->created_at }}" >
        <input id="department" name="department" type="hidden" value= "{{ $request[0]->employee->department->name }}" >
        <input id="task" name="task" type="hidden" value= "{{ $request[0]->task->name }}" >
        <input id="specifics" name="specifics" type="hidden" value= "{{ $request[0]->support_specifics }}" >
        <input id="technician" name="technician" type="hidden" value= "{{ $request[0]->technician->name ?? '' }}" >
        <table class="table table-hover">
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col">Fields</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>ID<input id="id" name="id" type="hidden" value= {{ $request[0]->id }} > </td>
                    <td>{{ $request[0]->id }}</td>
                </tr>
                <tr>
                    <td>Employee</td>
                    <td>{{ $request[0]->employee->name }}</td>
                </tr>
                <tr>
                    <td>Task</td>
                    <td>{{ $request[0]->task->name }}</td>
                </tr>
                <tr>
                    <td>Specifics</td>
                    <td>{{ $request[0]->support_specifics }}</td>
                </tr>
                <tr>
                    <td>Request Date</td>
                    <td>{{ $request[0]->created_at }}</td>
                </tr>
                <tr>
                    <td>Accomplish Date</td>
                    <td>
                        <input id="accDate" name="accDate" type="datetime-local" value= '{{ date('Y-m-d H:i:s') }}' step="any">
                    </td>
                </tr>
                <tr>
                    <td>Technician</td>
                    <td>
                        <select name="selectTechnician" id="selectTechnician" style="text-align-last:left" onchange="statusChange()">
                            @if($request[0]->technician == '')
                                <option value="" selected></option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            @else
                                <option value="" selected></option>
                                @foreach ($technicians as $tech)
                                    @if(optional($request[0]->technician)->id == $tech->id)
                                        <option value="{{ $tech->id }}" selected>{{ $tech->name }}</option>
                                    @else
                                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>
                        <select name="selectStatus" id="selectStatus" sytle="text-align-last:center" >
                            <?php $status = array("Pending", "Completed", "Cancelled") ?>
                            @foreach($status as $stat)
                                @if($stat === $request[0]->status)
                                    <option value="{{ $stat }}" selected="selected">{{ $stat }}</option>
                                @else
                                    <option value="{{ $stat }}">{{ $stat }}</option>
                                @endif
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Findings/Actions Taken</td>
                    <td>
                        <textarea id="findings"name="findings" style="width: 250px; height: 150px;"></textarea>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button class="btn btn-primary btn-lg" onclick="history.back()" type="button">&nbsp;&nbsp;Back&nbsp;&nbsp;</button>
                        <button id="submitBtn" name="submitBtn" class="btn btn-primary btn-lg" type="submit">&nbsp;&nbsp;Save&nbsp;&nbsp;</button>
                    </td>
                </tr>
            </tbody>
        </table>
        </form>
    </div>
</section>
@endsection 
