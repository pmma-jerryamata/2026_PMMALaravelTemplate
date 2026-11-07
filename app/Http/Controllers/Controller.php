<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Support;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function addEmployee(){
        $allDepartments = Department::all();
        return view('add-employee', ['departments' => $allDepartments]);
    }

    //One To One (Employee, Department)
    public function testCode(){
        $employees = Employee::all();
        foreach($employees as $employee){
            echo $employee->name."<br />";
            echo $employee->email."<br />";
            echo $employee->department->name."<br />";
        }
    }
    
    //One To Many (Employee, Support, Tasks)
    public function employeeSupport(){
        /*$supports = Support::with('employee')->get();
        dd($supports);
        foreach($supports as $support){
            echo $support->employee->name."<br />";
            echo $support->status."<br />";
        }*/
        $supports = Support::with('employee', 'task', 'technician')->get();
        //dd($supports);
        foreach($supports as $support){
            echo $support->employee->name." - <h4>".$support->task->name." : </h4>".$support->task->content
            ."<br />".$support->support_specifics
            ."<br />Technician : ".$support->technician->name."<hr />";
        }
    }
}
