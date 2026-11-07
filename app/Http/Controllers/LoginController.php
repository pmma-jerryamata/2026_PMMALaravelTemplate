<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Support;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LoginController extends Controller
{
    public function indexViews(Request $request){
        $ipAddress = $request->ip();
        /*$incomingFields = $request->validate([
            'email' => ['required', 'email']
        ]);*/
        $allTasks = Task::all();
        $error = "";
        return view('index', ['tasks'=>$allTasks, 'ip_address'=>$ipAddress, 'error'=>$error]);
    }

    public function jobRequest(Request $request){
        //$input = $request->all();
        //print_r($input);
        //dd($request);
        //load department database
        /*$emailEmployees = Employee::where('email','=',$request->emailProxy)->get();
        $userDepartment = Department::where('id','=', $emailEmployees[0]['department'])->get();*/
        $userTask = Task::where('id','=', $request->tasks1)->get();
        $allDepartments = Department::all();
        $employees = Employee::with('department')->where('email','=',$request->emailProxy)->get();
        $error = "";
        if($employees->isEmpty()){
            $allTasks = Task::all();
            $error = "Your PMMA E-mail account does not exist!";
            return view('index', ['tasks'=>$allTasks, 'error'=>$error]);
            dd($employees);
        }
        return view('info',['mytasks'=>$request,'departments' => $allDepartments, 'employee' => $employees[0], 'userTask' => $userTask[0]]);
    }
    
    public function studentView()
    {
        $departments = Department::all();
        return view('student', compact('departments'));
    }

    public function studentStore(Request $request)
    {
        $request->merge([
            'name' => mb_strtoupper(trim((string) $request->input('name'))),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);
        $studentDepartment = Department::whereRaw('UPPER(name) = ?', ['STUDENTS'])->value('id');
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'department_id' => ['required', 'integer', 'exists:departments,id', \Illuminate\Validation\Rule::in([$studentDepartment])],
        ]);
        $validated['name'] = strtoupper($validated['name']);
        $validated['email'] = strtolower($validated['email']);
        Employee::create($validated);

        return redirect()->route('student')->with('success', 'Student created successfully.');
    }
}
