<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceIssue extends Model
{
    protected $fillable = ['name', 'task_id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
