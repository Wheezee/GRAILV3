<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_type_id',
        'name',
        'max_score',
        'passing_score',
        'warning_score',
        'due_date',
        'description',
        'order',
        'term'
    ];

    protected $casts = [
        'due_date' => 'date',
        'max_score' => 'decimal:2',
        'passing_score' => 'decimal:2',
        'warning_score' => 'decimal:2',
    ];

    public function assessmentType()
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function annotations()
    {
        return $this->hasMany(AssessmentAnnotation::class);
    }

    public function getStudentScore($studentId)
    {
        return $this->scores()->where('student_id', $studentId)->first();
    }

    public function hasDueDate()
    {
        return !is_null($this->due_date);
    }

    public function subject()
    {
        return $this->assessmentType->subject;
    }
} 