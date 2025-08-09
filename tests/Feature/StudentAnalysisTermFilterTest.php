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
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentAnalysisTermFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $teacher;
    protected $subject;
    protected $classSection;
    protected $student;
    protected $quizType;
    protected $examType;

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
        $this->quizType = AssessmentType::create([
            'name' => 'Quiz',
            'subject_id' => $this->subject->id,
            'term' => 'midterm',
            'weight' => 30
        ]);

        $this->examType = AssessmentType::create([
            'name' => 'Exam',
            'subject_id' => $this->subject->id,
            'term' => 'final',
            'weight' => 70
        ]);
    }

    /** @test */
    public function it_only_shows_risk_factors_for_assessment_types_in_current_term()
    {
        // Create midterm quiz
        $midtermQuiz = Assessment::create([
            'name' => 'Quiz 1',
            'assessment_type_id' => $this->quizType->id,
            'max_score' => 100,
            'term' => 'midterm'
        ]);

        // Create final exam
        $finalExam = Assessment::create([
            'name' => 'Final Exam',
            'assessment_type_id' => $this->examType->id,
            'max_score' => 100,
            'term' => 'final'
        ]);

        // Add scores - quiz has low performance, exam has good performance
        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $midtermQuiz->id,
            'score' => 45, // Low score
            'is_late' => false
        ]);

        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $finalExam->id,
            'score' => 85, // Good score
            'is_late' => false
        ]);

        // Test midterm term analysis
        $response = $this->actingAs($this->teacher)
            ->get("/subjects/{$this->subject->id}/classes/{$this->classSection->id}/students/{$this->student->id}/analysis/midterm");

        $response->assertStatus(200);
        
        // Should show risk factor for low quiz performance
        $response->assertSee('Low Quiz Performance');
        
        // Should NOT show risk factor for exam performance (since exam is in final term)
        $response->assertDontSee('Low Exam Performance');

        // Test final term analysis
        $response = $this->actingAs($this->teacher)
            ->get("/subjects/{$this->subject->id}/classes/{$this->classSection->id}/students/{$this->student->id}/analysis/final");

        $response->assertStatus(200);
        
        // Should NOT show risk factor for quiz performance (since quiz is in midterm term)
        $response->assertDontSee('Low Quiz Performance');
        
        // Should NOT show risk factor for exam performance (since exam score is good)
        $response->assertDontSee('Low Exam Performance');
    }

    /** @test */
    public function it_returns_empty_risk_factors_for_term_with_no_assessments()
    {
        // Only create final exam, no midterm assessments
        $finalExam = Assessment::create([
            'name' => 'Final Exam',
            'assessment_type_id' => $this->examType->id,
            'max_score' => 100,
            'term' => 'final'
        ]);

        AssessmentScore::create([
            'student_id' => $this->student->id,
            'assessment_id' => $finalExam->id,
            'score' => 85,
            'is_late' => false
        ]);

        // Test midterm term analysis (should have no risk factors)
        $response = $this->actingAs($this->teacher)
            ->get("/subjects/{$this->subject->id}/classes/{$this->classSection->id}/students/{$this->student->id}/analysis/midterm");

        $response->assertStatus(200);
        
        // Should not show any assessment-specific risk factors
        $response->assertDontSee('Low Quiz Performance');
        $response->assertDontSee('Low Exam Performance');
    }
}
