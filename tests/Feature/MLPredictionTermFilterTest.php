<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Services\StudentMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MLPredictionTermFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $teacher;
    protected $subject;
    protected $classSection;
    protected $student;
    protected $midtermType;
    protected $finalType;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a teacher user
        $this->teacher = User::factory()->create([
            'user_type' => 'teacher'
        ]);

        // Create a subject
        $this->subject = Subject::create([
            'title' => 'Test Subject',
            'code' => 'TEST101',
            'units' => 3.0,
            'teacher_id' => $this->teacher->id
        ]);

        // Create a class section
        $this->classSection = ClassSection::create([
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->teacher->id,
            'section' => 'A',
            'schedule' => 'MWF 9:00-10:30'
        ]);

        // Create a student
        $this->student = Student::create([
            'student_id' => '2024-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com'
        ]);

        // Enroll student in class section
        $this->classSection->students()->attach($this->student->id);

        // Create assessment types for different terms
        $this->midtermType = AssessmentType::create([
            'name' => 'Midterm',
            'subject_id' => $this->subject->id,
            'term' => 'midterm',
            'weight' => 50
        ]);

        $this->finalType = AssessmentType::create([
            'name' => 'Final',
            'subject_id' => $this->subject->id,
            'term' => 'final',
            'weight' => 50
        ]);
    }

    /** @test */
    public function it_filters_metrics_by_midterm_term()
    {
        // Create midterm assessments
        $midtermAssessment1 = Assessment::create([
            'name' => 'Midterm 1',
            'assessment_type_id' => $this->midtermType->id,
            'max_score' => 100,
            'term' => 'midterm'
        ]);

        $midtermAssessment2 = Assessment::create([
            'name' => 'Midterm 2',
            'assessment_type_id' => $this->midtermType->id,
            'max_score' => 100,
            'term' => 'midterm'
        ]);

        // Create final assessment
        $finalAssessment = Assessment::create([
            'name' => 'Final Exam',
            'assessment_type_id' => $this->finalType->id,
            'max_score' => 100,
            'term' => 'final'
        ]);

        // Add scores for midterm assessments
        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $midtermAssessment1->id,
            'score' => 85,
            'is_late' => false
        ]);

        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $midtermAssessment2->id,
            'score' => 90,
            'is_late' => true
        ]);

        // Add score for final assessment
        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $finalAssessment->id,
            'score' => 95,
            'is_late' => false
        ]);

        $metricsService = new StudentMetricsService();
        
        // Get metrics for midterm term only
        $midtermMetrics = $metricsService->calculateStudentMetrics(
            $this->student->id, 
            $this->classSection->id, 
            'midterm'
        );

        // Get metrics for final term only
        $finalMetrics = $metricsService->calculateStudentMetrics(
            $this->student->id, 
            $this->classSection->id, 
            'final'
        );

        // Get metrics for all terms (no filter)
        $allMetrics = $metricsService->calculateStudentMetrics(
            $this->student->id, 
            $this->classSection->id, 
            null
        );

        // Assert midterm metrics only include midterm data
        $this->assertEquals(2, $midtermMetrics['total_assessments']);
        $this->assertEquals(2, $midtermMetrics['completed_assessments']);
        $this->assertEquals(87.5, $midtermMetrics['avg_score_pct']); // (85 + 90) / 2
        $this->assertEquals(50.0, $midtermMetrics['late_submission_pct']); // 1 out of 2

        // Assert final metrics only include final data
        $this->assertEquals(1, $finalMetrics['total_assessments']);
        $this->assertEquals(1, $finalMetrics['completed_assessments']);
        $this->assertEquals(95.0, $finalMetrics['avg_score_pct']);
        $this->assertEquals(0.0, $finalMetrics['late_submission_pct']);

        // Assert all metrics include all data
        $this->assertEquals(3, $allMetrics['total_assessments']);
        $this->assertEquals(3, $allMetrics['completed_assessments']);
        $this->assertEquals(90.0, $allMetrics['avg_score_pct']); // (85 + 90 + 95) / 3
        $this->assertEquals(33.3, round($allMetrics['late_submission_pct'], 1)); // 1 out of 3
    }

    /** @test */
    public function it_returns_empty_metrics_for_term_with_no_data()
    {
        // Create only final assessment
        $finalAssessment = Assessment::create([
            'name' => 'Final Exam',
            'assessment_type_id' => $this->finalType->id,
            'max_score' => 100,
            'term' => 'final'
        ]);

        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $finalAssessment->id,
            'score' => 95,
            'is_late' => false
        ]);

        $metricsService = new StudentMetricsService();
        
        // Get metrics for midterm term (should be empty)
        $midtermMetrics = $metricsService->calculateStudentMetrics(
            $this->student->id, 
            $this->classSection->id, 
            'midterm'
        );

        // Assert midterm metrics are empty
        $this->assertEquals(0, $midtermMetrics['total_assessments']);
        $this->assertEquals(0, $midtermMetrics['completed_assessments']);
        $this->assertEquals(0, $midtermMetrics['avg_score_pct']);
        $this->assertEquals(0, $midtermMetrics['late_submission_pct']);
    }
}
