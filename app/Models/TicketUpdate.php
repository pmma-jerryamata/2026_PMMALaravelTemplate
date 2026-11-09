<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketUpdate extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'actor_name', 'event', 'summary', 'action_taken', 'changes', 'created_at'];
    protected function casts(): array { return ['changes' => 'array', 'created_at' => 'datetime']; }
}
