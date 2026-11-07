<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingMeasure extends Model
{
    use HasFactory;
    protected $table = 'ratings_measure';
    protected $fillable = [
        'name',
        'description'
    ];
}
