<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAnnotation extends Model
{
    protected $fillable = [
        'student_id',
        'assessment_id',
        'teacher_id',
        'annotation_text',
        'annotation_type'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
