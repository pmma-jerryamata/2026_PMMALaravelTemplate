<?php

namespace App\Models;

use App\Models\Support;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    protected $table = 'tasks';
    protected $fillable = [
        'name',
        'content',
        'icon'
    ];

    public function support(){
        return $this->hasOne(Support::class);
    }
}
