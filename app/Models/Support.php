<?php

namespace App\Models;

use App\Models\Task;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Technician;
use App\Models\RatingResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Support extends Model
{
    use HasFactory;

    protected $table = 'supports';
    protected $fillable = [
        'employee_id',
        'technician_id',
        'task_id',
        'status',
        'support_specifics',
        'findings',
        'ip',
        'created_at',
        'updated_at'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function task(){
        return $this->belongsTo(Task::class);
    }

    public function technician(){
        return $this->belongsTo(Technician::class);
    }

    public function department(){
        return $this->hasOne(Department::class);
    }

    public function rating_result(){
        return $this->hasMany(RatingResult::class);
    }

}
