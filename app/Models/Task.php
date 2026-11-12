<?php

namespace App\Models;

use App\Models\Support;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    public function scopeDisplayOrder($query)
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('tasks', 'sort_order')) {
            $query->orderBy('sort_order');
        }
        return $query->orderBy('id');
    }
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
