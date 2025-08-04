<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'date',
        'status',
        'term',
    ];

    protected $casts = [
        'date' => 'date',
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
     * Check if the attendance record counts as present
     */
    public function isPresent(): bool
    {
        return in_array($this->status, ['present', 'late']);
    }

    /**
     * Check if the attendance record is late
     */
    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    /**
     * Check if the attendance record is absent
     */
    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }
} 