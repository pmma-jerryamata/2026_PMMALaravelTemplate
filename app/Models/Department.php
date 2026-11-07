<?php

namespace App\Models;

use App\Models\Support;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    protected $fillable = [
        'name'
    ];

    /*public function employee(){
        return $this->belongsTo(Employee::class);
    }*/
    public function employee(){
        return $this->hasOne(Employee::class);
    }

    public function support(){
        return $this->belongsTo(Support::class);
    }
}