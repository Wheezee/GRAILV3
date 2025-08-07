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

    /**
     * Check if this assessment is an attendance assessment
     */
    public function isAttendanceAssessment(): bool
    {
        return $this->assessmentType->name === 'Attendance';
    }

    /**
     * Get attendance records for this assessment
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Get attendance records for a specific student
     */
    public function getStudentAttendanceRecords($studentId)
    {
        return $this->attendanceRecords()->where('student_id', $studentId)->get();
    }

    /**
     * Calculate attendance score for a student
     */
    public function calculateAttendanceScore($studentId): ?float
    {
        if (!$this->isAttendanceAssessment()) {
            return null;
        }

        $records = $this->getStudentAttendanceRecords($studentId);
        if ($records->isEmpty()) {
            return null;
        }

        $totalDays = $records->count();
        $presentDays = $records->where('status', 'present')->count();
        $lateDays = $records->where('status', 'late')->count();

        // Calculate percentage: (present + late) / total * 100
        $attendancePercentage = (($presentDays + $lateDays) / $totalDays) * 100;
        
        return round($attendancePercentage, 2);
    }

    /**
     * Get attendance statistics for this assessment
     */
    public function getAttendanceStats()
    {
        $records = $this->attendanceRecords;
        $totalRecords = $records->count();
        
        if ($totalRecords === 0) {
            return [
                'total_days' => 0,
                'present_count' => 0,
                'absent_count' => 0,
                'late_count' => 0,
            ];
        }

        return [
            'total_days' => $records->groupBy('date')->count(),
            'present_count' => $records->where('status', 'present')->count(),
            'absent_count' => $records->where('status', 'absent')->count(),
            'late_count' => $records->where('status', 'late')->count(),
        ];
    }

    /**
     * Calculate the number of absences for a specific student
     */
    public function getStudentAbsenceCount($studentId): int
    {
        if (!$this->isAttendanceAssessment()) {
            return 0;
        }

        return $this->attendanceRecords()
            ->where('student_id', $studentId)
            ->where('status', 'absent')
            ->count();
    }

    /**
     * Calculate the total number of attendance days for a specific student
     */
    public function getStudentTotalAttendanceDays($studentId): int
    {
        if (!$this->isAttendanceAssessment()) {
            return 0;
        }

        return $this->attendanceRecords()
            ->where('student_id', $studentId)
            ->count();
    }

    /**
     * Calculate absences for a student based on their attendance score
     */
    public function getStudentAbsencesFromScore($studentId): ?array
    {
        if (!$this->isAttendanceAssessment()) {
            return null;
        }

        // Get the student's attendance score
        $score = $this->scores()->where('student_id', $studentId)->first();
        
        if (!$score || $score->percentage_score === null) {
            return null;
        }

        // Get total attendance days for this specific student
        $totalDays = $this->attendanceRecords()
            ->where('student_id', $studentId)
            ->count();

        if ($totalDays === 0) {
            return null;
        }

        // Calculate present days based on attendance percentage
        $presentDays = round(($score->percentage_score / 100) * $totalDays);
        $absences = $totalDays - $presentDays;

        return [
            'absences' => $absences,
            'total_days' => $totalDays,
            'attendance_percentage' => $score->percentage_score,
            'present_days' => $presentDays
        ];
    }
} 