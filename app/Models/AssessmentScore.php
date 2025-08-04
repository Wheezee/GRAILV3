<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'term',
        'score',
        'percentage_score',
        'is_late',
        'submitted_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'percentage_score' => 'decimal:2',
        'is_late' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Calculate and set the percentage score based on assessment passing score
     */
    public function calculatePercentageScore()
    {
        if ($this->score === null || $this->assessment->max_score == 0) {
            $this->percentage_score = null;
            return;
        }

        $rawPercentage = ($this->score / $this->assessment->max_score) * 100;
        
        // If passing score is set, adjust the percentage
        if ($this->assessment->passing_score !== null) {
            $passingPercentage = ($this->assessment->passing_score / $this->assessment->max_score) * 100;
            
            // Scale the percentage so that passing_score becomes 75%
            if ($rawPercentage >= $passingPercentage) {
                // Above passing: scale from passing% to 100%
                $this->percentage_score = 75 + (($rawPercentage - $passingPercentage) / (100 - $passingPercentage)) * 25;
            } else {
                // Below passing: scale from 0% to 75%
                $this->percentage_score = ($rawPercentage / $passingPercentage) * 75;
            }
        } else {
            // No passing score set, use raw percentage
            $this->percentage_score = $rawPercentage;
        }
        
        $this->percentage_score = round($this->percentage_score, 2);
    }
} 