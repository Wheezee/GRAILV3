<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'units',
        'schedule',
        'teacher_id',
    ];

    protected $casts = [
        'units' => 'decimal:1',
    ];

    /**
     * Get the teacher that owns the subject.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    public function gradingStructure()
    {
        return $this->hasOne(GradingStructure::class);
    }

    public function assessmentTypes()
    {
        return $this->hasMany(AssessmentType::class);
    }

    public function classSections()
    {
        return $this->hasMany(ClassSection::class);
    }

    public function classes()
    {
        return $this->classSections();
    }
} 