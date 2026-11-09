<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    public function scopeDirectoryOrder($query)
    {
        return $query->orderByRaw('CASE WHEN LOWER(name) = ? THEN 0 ELSE 1 END', ['students'])->orderBy('name');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(Technician::class);
    }
}
