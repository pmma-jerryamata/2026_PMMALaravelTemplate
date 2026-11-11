<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    
    //Inserted code for soft deletes//
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    public $timestamps = false; //used to avoid automated `created_at` query in support controller
    //////////////////////////////////
    
    protected $table = 'employees';
    
    protected $fillable = [
        'department_id',
        'name',
        'email',
        'deleted_at',
        'updated_at',
        'activation_pending',
    ];

    /*public function department(){
        return $this->hasOne(Department::class);
    }*/
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function support(){
        return $this->hasMany(Support::class);
    }
}
