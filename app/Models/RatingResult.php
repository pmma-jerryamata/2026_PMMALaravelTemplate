<?php

namespace App\Models;

use App\Models\Rating;
use App\Models\Support;
use App\Models\RatingMeasure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RatingResult extends Model
{
    use HasFactory;
    protected $table = 'ratings_result';
    protected $fillable = [
        'support_id',
        'rating_measure_id',
        'rating_id',
        'feedback',
        'created_at'
    ];

    public function support(){
        return $this->belongsTo(Support::class);
    }

    public function rating_measure(){
        return $this->belongsTo(RatingMeasure::class);
    }

    public function rating(){
        return $this->belongsTo(Rating::class);
    }
}
