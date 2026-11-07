<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $employees = Employee::all();
        return view('admin.employee', ['request'=>$employees]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getEmployees(Request $name)
    {
        $employees = Employee::where('name', 'like', '%' . $name->searchVal . '%')->limit(5)->get();
        return $employees;   
    }

    public function getEmployeesWithTrashed(Request $name)
    {
        $employees = Employee::where('name', 'like', '%' . $name->searchVal . '%')->limit(5)->withTrashed()->get();
        return $employees;   
    }

    public function getEmployeesDepartment(Request $request)
    {
        //print_r($request);
    
        $employee = Employee::withTrashed()
                    ->with('department')
                    ->where('id', $request->searchVal)
                    ->get();
        //$departments = Department::all();
        $departments = Department::orderBy('name', 'asc')->get();
        
        return ['employee'=>$employee, 'departments'=>$departments];
    }

    public function updateEmployee(Request $request)
    {
        //print($request);
        $message = "Something went wrong while updating the data. Contact the System Administrator.";
        if($request->status == "ACTIVE"){
            //$result = Employee::withTrashed()->where('id', $request->id)->restore();
            $result = Employee::withTrashed()->where('id', $request->id)->update(['name'=>$request->name, 'email'=>$request->email, 'department_id'=>$request->department_id, 'deleted_at'=>null]);
            
        }else{
            $result = Employee::where('id', $request->id)->update(['name'=>$request->name, 'email'=>$request->email, 'department_id'=>$request->department_id, 'deleted_at'=>date("Y-m-d h:i:s")]);
        }
        
        //Employee::where('id', $request->id)->update(['email'=>$request->email]);
        if($result){
            $message = "Update Successful!";
        }
        return $message;
    }

    public function getDepartments()
    {
        //$departments = Department::all();
        $departments = Department::orderBy('name', 'asc')->get();
        return $departments;
    }

    public function createEmployee(Request $request)
    {
        $name = $request->empName;
        $email = $request->empEmail;
        $department = $request->empDepartment;

        $checkEmail = Employee::where('email', $email)->first();

        if($checkEmail){
            $message = "Error: [".$email."] Already Exists!";
        }else{
            $add = Employee::create([
                'name' => $name,
                'email' => $email,
                'department_id' => $department
            ]);
             $message = "Successfuly Added To The Database!";
        }
        return $message;
    }

    public function removeEmployee(Request $request)
    {
        $id = $request->id;
        $removeId = Employee::where('id', $id)->delete();

        return "Successfuly Deleted From The Database! ID#: " . $id;
    }
}