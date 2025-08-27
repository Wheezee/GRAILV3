<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'section',
        'schedule',
        'classroom',
        'student_count',
        'teacher_id',
        'grading_settings',
    ];

    protected $casts = [
        'grading_settings' => 'array',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_section_student')
                    ->withPivot('enrollment_date', 'status')
                    ->withTimestamps();
    }
}
