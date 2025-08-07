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

    /**
     * Check if this subject has attendance assessment types
     */
    public function hasAttendanceAssessment(): bool
    {
        return $this->assessmentTypes()
            ->where('name', 'Attendance')
            ->exists();
    }

    /**
     * Get attendance assessment types for this subject
     */
    public function getAttendanceAssessmentTypes()
    {
        return $this->assessmentTypes()
            ->where('name', 'Attendance')
            ->get();
    }

    /**
     * Calculate total absences for a student across all attendance assessments
     */
    public function getStudentTotalAbsences($studentId, $term = null): array
    {
        $attendanceTypes = $this->getAttendanceAssessmentTypes();
        $totalAbsences = 0;
        $totalDays = 0;
        $termAbsences = [];

        foreach ($attendanceTypes as $assessmentType) {
            // If term is specified, only count for that term
            if ($term && $assessmentType->term !== $term) {
                continue;
            }

            $assessments = $assessmentType->assessments;
            foreach ($assessments as $assessment) {
                // Try to get absences from score first (more reliable)
                $absenceData = $assessment->getStudentAbsencesFromScore($studentId);
                
                if ($absenceData) {
                    $totalAbsences += $absenceData['absences'];
                    $totalDays += $absenceData['total_days'];
                    
                    if ($term) {
                        $termAbsences[$assessmentType->term] = [
                            'absences' => $absenceData['absences'],
                            'total_days' => $absenceData['total_days'],
                            'attendance_percentage' => $absenceData['attendance_percentage']
                        ];
                    }
                } else {
                    // Fallback to direct counting if no score exists
                    $absenceCount = $assessment->getStudentAbsenceCount($studentId);
                    $totalDaysCount = $assessment->getStudentTotalAttendanceDays($studentId);
                    
                    $totalAbsences += $absenceCount;
                    $totalDays += $totalDaysCount;
                    
                    if ($term) {
                        $termAbsences[$assessmentType->term] = [
                            'absences' => $absenceCount,
                            'total_days' => $totalDaysCount
                        ];
                    }
                }
            }
        }

        return [
            'total_absences' => $totalAbsences,
            'total_days' => $totalDays,
            'term_breakdown' => $termAbsences
        ];
    }
} 