<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'class_section_id',
        'birth_date',
        'gender',
        'contact_number',
        'address',
    ];

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function classSections()
    {
        return $this->belongsToMany(ClassSection::class, 'class_section_student')
                    ->withPivot('enrollment_date', 'status')
                    ->withTimestamps();
    }

    public function assessmentScores()
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function annotations()
    {
        return $this->hasMany(AssessmentAnnotation::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
} 