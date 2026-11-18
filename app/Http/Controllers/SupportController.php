<?php

namespace App\Http\Controllers;

use console;
use App\Mail\Mailer;
use App\Models\Task;
use App\Mail\MailerIT;
use App\Models\Support;
use App\Models\Employee;
use App\Mail\MailerStatus;
use App\Models\Department;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class SupportController extends Controller
{
    public function view(){
        return view('support', []);
    }

    public function ticket(){
        return view('ticket', []);
    }

    public function phone(){
        return view('phone', []);
    }

    public function saveSpecificSupport(Request $request){
        //dd($request);
        //Support::where('id', $request->id)->update(['status'=>$request->selectStatus]);
        //Support::where('id', $request->id)->update(['technician_id'=>$request->selectTechnician]);
        //Support::where('id', $request->id)->update(['updated_at'=>$request->accDate]);

        $result = Support::where('id', $request->id)
        ->update([
            'status'=> $request->selectStatus, 
            'technician_id'=> $request->selectTechnician, //id of technician
            'findings' => $request->findings,
            'updated_at' => $request->accDate
        ]);

        $_emailAddress = $request->email;
        $_ticketNumber = $request->id;
        $_status = $request->selectStatus;
        $_dateRequest = $request->created_at;
        $_name = $request->name;
        $_department = $request->department;

        $_task = $request->task;
        $_specifics = $request->specifics;
        $_findings = $request->findings;

        $_technician = $request->technician; //name of technician
        $_dateAccomplished = $request->accDate;
        ////////////////////////////////////////////////////////////////////////////SEND E-MAIL CONFIRMATION TO CLIENT//
        //Mail::to($request['email'])->send(new MailerStatus($request->id, $request->selectStatus));
        $event = $_status === 'Completed' ? 'completed' : 'updated';
        if ($result && config('mail.ticket_notifications.'.$event)) {
            Mail::to($_emailAddress)->send(new MailerStatus($_ticketNumber, $_status, $_dateRequest, $_name, $_department, $_task, $_specifics, $_findings, $_technician, $_dateAccomplished, $event));
            //Mail::to('ma.amoroso@pmma.edu.ph')->send(new MailerStatus($_ticketNumber, $_status, $_dateRequest, $_name, $_department, $_task, $_specifics, $_findings, $_technician, $_dateAccomplished));
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        return view('success');
    }

    public function getSpecificSupport(Request $request){
        
        $supports = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
        ])->where('id',$request->passValue)->get();
        //print_r($supports);
        $technicians = Technician::all();
        //print( $supports[1]);
        //print_r($supports);
        //print_r($supports[0]['technician_id']);
        //$supports['tech'] = Technician::where('id', $supports['supp'])->get();
        //print($request->passValue);
        return view('edit', ['request'=>$supports, 'technicians'=>$technicians]);
    }
    public function adminGetSupport(Request $request){
        $pageSize = $request->input('pageSize', 10);
        $tasks = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])->orderBy('created_at', 'desc')->paginate($pageSize);
    
        return response()->json([
            'support' => $tasks->items(),
            'total' => $tasks->total()
        ]);
    }
    public function adminGetSupportPending(Request $request){
        $pageSize = $request->input('pageSize', 10);
        $tasks = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])->where('status','Pending')->orderBy('created_at', 'desc')->paginate($pageSize);

        $tech = Technician::all();

        return response()->json([
            'support' => $tasks->items(),
            'tech' => $tech,
            'total' => $tasks->total()
        ]);
    }
    public function adminGetSupportCancelled(Request $request){
        $pageSize = $request->input('pageSize', 10);
        $tasks = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])->where('status','Cancelled')->orderBy('created_at', 'desc')->paginate($pageSize);

        $tech = Technician::all();

        return response()->json([
            'support' => $tasks->items(),
            'tech' => $tech,
            'total' => $tasks->total()
        ]);
    }
    public function adminGetSupportCompleted(Request $request){
        $pageSize = $request->input('pageSize', 10);
        $tasks = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])->where('status','Completed')->orderBy('created_at', 'desc')->paginate($pageSize);

        $tech = Technician::all();

        return response()->json([
            'support' => $tasks->items(),
            'tech' => $tech,
            'total' => $tasks->total()
        ]);
    }
    public function getSupport(Request $order){
        
        $pageSize = $order->pageSize;
        $ord = 'desc';
        if($order->order[0]['value'] % 2){
            $ord = 'asc';
        }

        //$supports = Task::orderBy('created_at', 'desc')->paginate($pageSize);
        //dd($order);
        /*
        $ord = 'desc';
        if($order->order[0]['value'] % 2){
            $ord = 'asc';
        }
        //dd($order);
        $supports['supp'] = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])->orderBy('created_at', $ord)->get();
        $supports['tech'] = Technician::all();
        */


        
        $supports['supp'] = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])->orderBy('created_at', $ord)->paginate($pageSize);
        $supports['tech'] = Technician::all();
        $supports['pageSize'] = $pageSize;
        $supports['total'] = $supports['supp']->total();
        //return $supports;
        /*
        return response()->json([
            'supp' => $supports->items(),
            'tech' => $techs->items(),
            'total' => $supports->total()
        ]);
        */
        return $supports;

    }
    public function getSupportPending(Request $order){
        $ord = 'desc';
        if($order->order[1]['value'] % 2){
            $ord = 'asc';
        }
        $supports['supp'] = Support::with([
            'technician', 
            'task', 
            'employee' => function ($query) {
                $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
            }
            ])
            ->where('status','Pending')->orderBy('created_at', $ord)->get();
        $supports['tech'] = Technician::all();
        $supports['size'] = sizeOf(Support::with('technician', 'task', 'employee.department')->where('status','Pending')->get());
        return $supports;
    }
    public function getSupportPendingTech(Request $order){
        $ord = 'asc';
        if($order->order[0]['value'] % 2){
            $ord = 'asc';
        }
        $supports['supp'] = Support::with(
            [
                'technician', 
                'task', 
                'employee' => function ($query) {
                    $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
                }
            ])
            ->where('status','Pending')->orderBy('created_at', $ord)->get();
        $supports['tech'] = Technician::all();
        $supports['size'] = sizeOf(Support::with('technician', 'task', 'employee.department')->where('status','Pending')->get());
        return $supports;
    }
    public function getSupportPending2(Request $order){
        $ord = 'desc';
        if($order->order[1]['value'] % 2){
            $ord = 'asc';
        }
        $supports['supp'] = Support::withTrashed()->with('technician', 'task', 'employee.department')->where('status','Pending')->orderBy('created_at', $ord)->get();
        $supports['tech'] = Technician::all();
        return $supports;
    }
    public function getSupportCompleted(Request $order){
        $ord = 'desc';
        if($order->order[3]['value'] % 2){
            $ord = 'asc';
        }
        $supports['supp'] = Support::with(
            [
                'technician', 
                'task', 
                'employee' => function ($query) {
                    $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
                }
            ])
            ->where('status','Completed')->orderBy('created_at', $ord)->get();
        $supports['tech'] = Technician::all();
        return $supports;
    }
    public function getSupportCancelled(Request $order){
        $ord = 'desc';
        if($order->order[2]['value'] % 2){
            $ord = 'asc';
        }
        $supports['supp'] = Support::with(
            [
                'technician', 
                'task', 
                'employee' => function ($query) {
                    $query->withTrashed()->with('department'); // Include soft-deleted employees and load the department relationship
                }
            ])
            ->where('status','Cancelled')->orderBy('created_at', $ord)->get();
        $supports['tech'] = Technician::all();
        return $supports;
    }

    public function getTechnician(){
        $technicians = Technician::all();
        //print_r($supports);
        
        return $technicians;
    }
    
    public function viewSupport(){
        //$fromSupportTable = Support::with(['employees'])->get();
        $fromSupportTable = Support::all();
        //print_r($fromSupportTable);
        foreach($fromSupportTable as $fromSupport){
            $nameEmployee = Employee::where('id',$fromSupport->user_id)->get('name');
            //$fromSupport['user_id'] = $nameEmployee[0]['name'];

            $nameResponder = Technician::where('id',$fromSupport->tech_id)->get('name');
            $fromSupport['tech_id'] = $nameResponder[0]['name'];

            $nameDepartment = Department::where('id',$fromSupport->user_id)->get('name');
            $fromSupport['user_id'] = $nameEmployee[0]['name'] ." - ". $nameDepartment[0]['name'];
        };
        //for($i = 1; $i <= count($fromSupportTable); $i++){
            //$nameEmployee = Employee::where('id',$fromSupportTable)->get('name');
            //$fromSupportTable['user_id'] = $nameEmployee[0]['name'];
        //};
     
        //$empName = Employee::where('id',$fromSupportTable->user_id)->get();
        $fromTechnicianTable = Technician::all();

        return view('support', ['support'=>$fromSupportTable, 'technician'=>$fromTechnicianTable]);
    }
    public function reviewSupport(Request $request){
        echo "POST : ".$request->timeFinished;
        $request->whenFilled('testing', function (string $input) {
            // The "name" value is filled...
            echo "True";
        }, function () {
            // The "name" value is not filled...
            echo "False";
        });
        //$fromSupportTable = Support::with(['employees'])->get();
        $fromSupportTable = Support::all();
        //print_r($fromSupportTable);
        foreach($fromSupportTable as $fromSupport){
            $nameEmployee = Employee::where('id',$fromSupport->user_id)->get('name');
            $nameResponder = Technician::where('id',$fromSupport->tech_id)->get('name');
            $fromSupport['tech_id'] = $nameResponder[0]['name'];
            $nameDepartment = Department::where('id',$fromSupport->user_id)->get('name');
            $fromSupport['user_id'] = $nameEmployee[0]['name'] ." - ". $nameDepartment[0]['name'];
        };
        $fromTechnicianTable = Technician::all();

        return view('support', ['support'=>$fromSupportTable, 'technician'=>$fromTechnicianTable, 'time'=>$request->timeFinished]);
    }

    public function notifyUser(Request $request){
        //dd($request);
        //$nameEmployee = Employee::where('email',$request->email)->get();
        //$status = "Pending";
        //$support_type = $request->task;
        //$support_specifics = $request->specificsField;
        $request->validate([
            'submission_token' => 'required',
            // Other validations
        ]);

        // Check if submission_token exists in session
        if (Session::has($request->input('submission_token'))) {
            return redirect()->route('index'); //return to Main Page
        }

        // Store the submission_token in session to prevent re-entry
        Session::put($request->input('submission_token'), true);

        $support = Support::create([
            'employee_id' => $request->empId,
            'status' => "Pending",
            'task_id' => $request->task,
            'technician_id' => null,
            'support_specifics' => $request->specificsField,
            'ip' => $request->ip()
        ]);
        $assignedId = $support->id;
        //$lastId = Support::max('id');
        $emailEmployee = Employee::where('id', $request->empId)->get('email');
        //print($emailEmployee[0]['email']);
        ////////////////////////////////////////////////////////////////////////////SEND E-MAIL CONFIRMATION TO CLIENT//
        Mail::to($emailEmployee[0]['email'])->send(new Mailer($assignedId));
        //Mail::to('ma.amoroso@pmma.edu.ph')->send(new Mailer($assignedId));
        //Mail::to('itsupport@pmma.edu.ph')->send(new MailerIT($assignedId));
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        return view('notify', ['users'=>$request, 'ticket'=>$assignedId]);
    }

    private function queueTable($request, $nameEmployee){
        //print_r($request);
        $status = "Pending";
        $support_type = $request->task;
        $support_specifics = $request->specificsField;
        $support = Support::create([
            'user_id' => $nameEmployee[0]['id'],
            'status' => $status,
            'support_type' => $support_type,
            'support_specifics' =>  $support_specifics,
            'created_at' => "",
            'updated_at' => "2024-02-20T10:31",
            'tech_id' => 1
        ]);

        //$support = new Support;
        //$support->user_id = $nameEmployee[0]['id'];
        //$support->id = $request[0]['name'];
        //$support->save();
    }

    public function axiosSupport(){
        $suppss = Support::all();
        //$supps = Employee::with('users')->get();
        foreach($suppss as $supp){
            echo $suppss->technicians->name;
        }
        return response()->json($suppss);
    }

    public function addSupport(){
        //dd($request);
        //$userTask = Task::where('id','=', $request->tasks1)->get();
        //$allDepartments = Department::all();
        $allDepartments = Department::orderBy('name', 'asc')->get();
        $allEmployees = Employee::with('department')->get();
        $allTasks = Task::all();
        $allTechnicians = Technician::all();
        //print_r($allEmployees);
        //$error = "";
        //if($employees->isEmpty()){
        //    $allTasks = Task::all();
        //    $error = "Your PMMA E-mail account does not exist!";
        //    return view('index', ['tasks'=>$allTasks, 'error'=>$error]);
        //    dd($employees);
        //}
        return view('add', ['employees'=>$allEmployees,'departments' => $allDepartments, 'tasks' => $allTasks, 'technicians' => $allTechnicians]);
    }
    //CREATE TICKET FOR ADMIN PANEL
    public function saveSupportAdmin(Request $request){
        //dd($request);
        /////////////////////////////////////////////////////////////////////////////////////////////////
        //This is to avoid duplicated entry via refresh
        $request->validate([
            '_token' => 'required', // Ensure the token is included
        ]);
    
        // Step 2: Check if the token is already used
        if (Session::has('_used_tokens.' . $request->_token)) {
            return redirect()->route('support');
        }
    
        // Step 3: Mark the token as used
        Session::put('_used_tokens.' . $request->_token, true);
        /////////////////////////////////////////////////////////////////////////////////////////////////
        
        //dd($request);

        $employeeData = explode(",",$request->selectEmployee);
        $request->validate([
            'selectEmployee' => 'required',
            'selectTask' => 'required|integer',
        ]);

        $support = Support::create([
            //'employee_id' => $request->selectEmployee,
            'employee_id' => $employeeData[0],
            'status' => "Pending",
            'task_id' => $request->selectTask,
            'technician_id' => $request->selectTechnician,
            'support_specifics' => $request->specificsField,
            'ip' => $request->ip()
        ]);
        
        //$lastId = Support::max('id');
        $assignedId = $support->id;

        //////////////////////////////SEND E-MAIL CONFIRMATION TO CLIENT////////////////////////////////////////////////
        Mail::to($employeeData[1])->send(new Mailer($assignedId));
        //Mail::to('ma.amoroso@pmma.edu.ph')->send(new Mailer($assignedId));
        //Mail::to('itsupport@pmma.edu.ph')->send(new MailerIT($assignedId));
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        return view('success');
    }
    //TEMPORARY FUNCTION FOR RETRIEVING REPORT
    public function reportSupportAdmin()
    {
        return app(ReportController::class)->index(request());
    }
    //CREATE TICKET FOR TECHNICAN
    public function createTicket(Request $request){
        $support = Support::create([
            'employee_id' => $request->empId,
            'status' => "Pending",
            'technician_id' => $request->techId,
            'task_id' => $request->task,
            'support_specifics' => $request->specificsField,
            'ip' => $request->ip()
        ]);

        //$lastId = Support::max('id');
        $assignedId = $support->id;

        $emailEmployee = Employee::where('id', $request->empId)->get('email');
        //print($emailEmployee[0]['email']);
        ////////////////////////////////////////////////////////////////////////////SEND E-MAIL CONFIRMATION TO CLIENT//
        Mail::to($emailEmployee[0]['email'])->send(new Mailer($assignedId));
        //Mail::to('ma.amoroso@pmma.edu.ph')->send(new Mailer($assignedId));
        //Mail::to('itsupport@pmma.edu.ph')->send(new MailerIT($assignedId));
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        return "Successfully created ticket number : ".$assignedId;
    }

    public function supportUpdate(Request $request){
        
        //dd($request);
        
        $result = Support::where('id', $request->taskId)
        ->update([
            'status'=> $request->status, 
            'technician_id'=> $request->techId, 
            'findings' => $request->findings,
            'updated_at' => date("Y-m-d H:i:s")
        ]);
        //Support::where('id', $request->id)->update(['technician_id'=>$request->selectTechnician]);
        //Support::where('id', $request->id)->update(['updated_at'=>$request->accDate]);

        //$emailEmployee = Support::with('employee')->where('id', $request->taskId)->get();
        //dd($emailEmployee);
        if(!$result){
            return "Error [SC - SupportUpdate] : Something went wrong. Contact your system administrator.";
        }
        
        $emailEmployee = Support::with('employee','task','employee.department', 'technician')->where('id', $request->taskId)->get();

        $_emailAddress = $emailEmployee[0]['employee']->email;
        $_ticketNumber = $emailEmployee[0]->id;
        $_status = $emailEmployee[0]->status;
        $_dateRequest = $emailEmployee[0]->created_at;
        $_name = $emailEmployee[0]['employee']->name;
        $_department = $emailEmployee[0]['employee']->department->name;

        $_task = $emailEmployee[0]['task']->name;
        $_specifics = $emailEmployee[0]->support_specifics;
        $_findings = $emailEmployee[0]->findings;

        $_technician = optional($emailEmployee[0]['technician'])->name ?? 'No Technician Assigned';
        $_dateAccomplished = $emailEmployee[0]->updated_at;

        //print($_emailAddress . ", " . $_ticketNumber . ", " . $_status . ", " . $_dateRequest . ", " . $_department . ", " . $_name . ", " . $_task . ", " . $_specifics . ", " . $_findings . ", " . $_technician . ", " . $_dateAccomplished);
        ////////////////////////////////////////////////////////////////////////////SEND E-MAIL CONFIRMATION TO CLIENT//
        //Mail::to($emailEmployee[0]['employee']->email)->send(new MailerStatus($request->taskId, $request->status));
        $event = $_status === 'Completed' ? 'completed' : 'updated';
        if (config('mail.ticket_notifications.'.$event)) {
            Mail::to($_emailAddress)->send(new MailerStatus($_ticketNumber, $_status, $_dateRequest, $_name, $_department, $_task, $_specifics, $_findings, $_technician, $_dateAccomplished, $event));
        }
        //Mail::to('ma.amoroso@pmma.edu.ph')->send(new MailerStatus($_ticketNumber, $_status, $_dateRequest, $_name, $_department, $_task, $_specifics, $_findings, $_technician, $_dateAccomplished));
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
        return "Ticket No. ".$_ticketNumber." has been successfully updated.";
        //return $emailEmployee[0]['employee']->email;
    }

    public function getEmployees(Request $request){
        $result = Employee::where('department_id',$request->departmentId)->orderBy('name', 'asc')->get();
        return $result;
    }

    public function getTasks(Request $request){
        $result = Task::all();
        return $result;
    }
}
