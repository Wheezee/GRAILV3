<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'type',
        'midterm_weight',
        'final_weight',
    ];

    protected $casts = [
        'midterm_weight' => 'decimal:2',
        'final_weight' => 'decimal:2',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function assessmentTypes()
    {
        return $this->hasMany(AssessmentType::class, 'subject_id', 'subject_id');
    }
} 