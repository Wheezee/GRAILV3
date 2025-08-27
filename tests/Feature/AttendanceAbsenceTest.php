<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\AssessmentType;
use App\Models\Assessment;
use App\Models\AttendanceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceAbsenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_has_attendance_assessment()
    {
        // Create a teacher
        $teacher = User::factory()->create(['user_type' => 'teacher']);
        
        // Create a subject
        $subject = Subject::create([
            'code' => 'TEST101',
            'title' => 'Test Subject',
            'units' => 3.0,
            'teacher_id' => $teacher->id,
        ]);

        // Create attendance assessment type
        $attendanceType = AssessmentType::create([
            'subject_id' => $subject->id,
            'name' => 'Attendance',
            'term' => 'midterm',
            'weight' => 10.0,
            'order' => 1,
        ]);

        $this->assertTrue($subject->hasAttendanceAssessment());
    }

    public function test_calculate_student_absences()
    {
        // Create a teacher
        $teacher = User::factory()->create(['user_type' => 'teacher']);
        
        // Create a subject
        $subject = Subject::create([
            'code' => 'TEST101',
            'title' => 'Test Subject',
            'units' => 3.0,
            'teacher_id' => $teacher->id,
        ]);

        // Create attendance assessment type
        $attendanceType = AssessmentType::create([
            'subject_id' => $subject->id,
            'name' => 'Attendance',
            'term' => 'midterm',
            'weight' => 10.0,
            'order' => 1,
        ]);

        // Create attendance assessment
        $assessment = Assessment::create([
            'assessment_type_id' => $attendanceType->id,
            'name' => 'Attendance',
            'max_score' => 100,
            'term' => 'midterm',
        ]);

        // Create a student
        $student = Student::create([
            'student_id' => '2021-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Create attendance records
        AttendanceRecord::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'date' => '2024-01-01',
            'status' => 'present',
            'term' => 'midterm',
        ]);

        AttendanceRecord::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'date' => '2024-01-02',
            'status' => 'absent',
            'term' => 'midterm',
        ]);

        AttendanceRecord::create([
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'date' => '2024-01-03',
            'status' => 'absent',
            'term' => 'midterm',
        ]);

        // Test absence calculation
        $absenceData = $subject->getStudentTotalAbsences($student->id, 'midterm');
        
        $this->assertEquals(2, $absenceData['total_absences']);
        $this->assertEquals(3, $absenceData['total_days']);
        $this->assertEquals(2, $assessment->getStudentAbsenceCount($student->id));
    }

    public function test_calculate_absences_from_score()
    {
        // Create a teacher
        $teacher = User::factory()->create(['user_type' => 'teacher']);
        
        // Create a subject
        $subject = Subject::create([
            'code' => 'TEST102',
            'title' => 'Test Subject 2',
            'units' => 3.0,
            'teacher_id' => $teacher->id,
        ]);

        // Create attendance assessment type
        $attendanceType = AssessmentType::create([
            'subject_id' => $subject->id,
            'name' => 'Attendance',
            'term' => 'midterm',
            'weight' => 10.0,
            'order' => 1,
        ]);

        // Create attendance assessment
        $assessment = Assessment::create([
            'assessment_type_id' => $attendanceType->id,
            'name' => 'Attendance',
            'max_score' => 100,
            'term' => 'midterm',
        ]);

        // Create a student
        $student = Student::create([
            'student_id' => '2021-0002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        // Create attendance records (9 total days, 1 present, 8 absent = 11.11% attendance)
        for ($i = 1; $i <= 9; $i++) {
            $status = $i === 1 ? 'present' : 'absent'; // Only 1st day present
            AttendanceRecord::create([
                'assessment_id' => $assessment->id,
                'student_id' => $student->id,
                'date' => "2024-01-0{$i}",
                'status' => $status,
                'term' => 'midterm',
            ]);
        }

        // Create assessment score (11.11% attendance)
        $assessment->scores()->create([
            'student_id' => $student->id,
            'term' => 'midterm',
            'score' => 11.11,
            'percentage_score' => 11.11,
            'submitted_at' => now(),
        ]);

        // Test absence calculation from score
        $absenceData = $assessment->getStudentAbsencesFromScore($student->id);
        
        $this->assertEquals(8, $absenceData['absences']); // 8 absences
        $this->assertEquals(9, $absenceData['total_days']); // 9 total days
        $this->assertEquals(11.11, $absenceData['attendance_percentage']); // 11.11% attendance
        $this->assertEquals(1, $absenceData['present_days']); // 1 present day
    }

    public function test_subject_without_attendance_returns_zero_absences()
    {
        // Create a teacher
        $teacher = User::factory()->create(['user_type' => 'teacher']);
        
        // Create a subject without attendance
        $subject = Subject::create([
            'code' => 'TEST101',
            'title' => 'Test Subject',
            'units' => 3.0,
            'teacher_id' => $teacher->id,
        ]);

        // Create a student
        $student = Student::create([
            'student_id' => '2021-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        // Test absence calculation for subject without attendance
        $absenceData = $subject->getStudentTotalAbsences($student->id);
        
        $this->assertEquals(0, $absenceData['total_absences']);
        $this->assertEquals(0, $absenceData['total_days']);
    }

    public function test_correct_total_days_calculation()
    {
        // Create a teacher
        $teacher = User::factory()->create(['user_type' => 'teacher']);
        
        // Create a subject
        $subject = Subject::create([
            'code' => 'TEST103',
            'title' => 'Test Subject 3',
            'units' => 3.0,
            'teacher_id' => $teacher->id,
        ]);

        // Create attendance assessment type
        $attendanceType = AssessmentType::create([
            'subject_id' => $subject->id,
            'name' => 'Attendance',
            'term' => 'midterm',
            'weight' => 10.0,
            'order' => 1,
        ]);

        // Create attendance assessment
        $assessment = Assessment::create([
            'assessment_type_id' => $attendanceType->id,
            'name' => 'Attendance',
            'max_score' => 100,
            'term' => 'midterm',
        ]);

        // Create two students
        $student1 = Student::create([
            'student_id' => '2021-0003',
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
        ]);

        $student2 = Student::create([
            'student_id' => '2021-0004',
            'first_name' => 'Bob',
            'last_name' => 'Wilson',
        ]);

        // Create attendance records for student 1 (10 days)
        for ($i = 1; $i <= 10; $i++) {
            $status = $i <= 8 ? 'present' : 'absent'; // 8 present, 2 absent
            $date = $i < 10 ? "2024-01-0{$i}" : "2024-01-{$i}";
            AttendanceRecord::create([
                'assessment_id' => $assessment->id,
                'student_id' => $student1->id,
                'date' => $date,
                'status' => $status,
                'term' => 'midterm',
            ]);
        }

        // Create attendance records for student 2 (5 days)
        for ($i = 1; $i <= 5; $i++) {
            $status = $i <= 3 ? 'present' : 'absent'; // 3 present, 2 absent
            $date = "2024-01-1{$i}";
            AttendanceRecord::create([
                'assessment_id' => $assessment->id,
                'student_id' => $student2->id,
                'date' => $date,
                'status' => $status,
                'term' => 'midterm',
            ]);
        }

        // Create assessment scores
        $assessment->scores()->create([
            'student_id' => $student1->id,
            'term' => 'midterm',
            'score' => 80.0, // 8/10 = 80%
            'percentage_score' => 80.0,
            'submitted_at' => now(),
        ]);

        $assessment->scores()->create([
            'student_id' => $student2->id,
            'term' => 'midterm',
            'score' => 60.0, // 3/5 = 60%
            'percentage_score' => 60.0,
            'submitted_at' => now(),
        ]);

        // Test absence calculation for student 1
        $absenceData1 = $assessment->getStudentAbsencesFromScore($student1->id);
        $this->assertEquals(2, $absenceData1['absences']); // 2 absences
        $this->assertEquals(10, $absenceData1['total_days']); // 10 total days
        $this->assertEquals(80.0, $absenceData1['attendance_percentage']); // 80% attendance

        // Test absence calculation for student 2
        $absenceData2 = $assessment->getStudentAbsencesFromScore($student2->id);
        $this->assertEquals(2, $absenceData2['absences']); // 2 absences
        $this->assertEquals(5, $absenceData2['total_days']); // 5 total days
        $this->assertEquals(60.0, $absenceData2['attendance_percentage']); // 60% attendance

        // Test total absences from subject
        $totalAbsences1 = $subject->getStudentTotalAbsences($student1->id, 'midterm');
        $this->assertEquals(2, $totalAbsences1['total_absences']);
        $this->assertEquals(10, $totalAbsences1['total_days']);

        $totalAbsences2 = $subject->getStudentTotalAbsences($student2->id, 'midterm');
        $this->assertEquals(2, $totalAbsences2['total_absences']);
        $this->assertEquals(5, $totalAbsences2['total_days']);
    }
} 