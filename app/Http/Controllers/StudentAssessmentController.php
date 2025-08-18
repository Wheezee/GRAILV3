<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentToken;
use App\Models\AssessmentScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class StudentAssessmentController extends Controller
{
    /**
     * Show access form for assessment
     */
    public function showAccessForm($uniqueUrl)
    {
        $assessment = Assessment::where('unique_url', $uniqueUrl)
            ->where('is_quiz', true)
            ->firstOrFail();

        return view('student.assessment-access', compact('assessment'));
    }

    /**
     * Validate token and start assessment
     */
    public function validateToken(Request $request, $uniqueUrl)
    {
        $assessment = Assessment::where('unique_url', $uniqueUrl)
            ->where('is_quiz', true)
            ->firstOrFail();

        // Check if quiz has expired
        if ($assessment->isExpired()) {
            return back()->with('error', 'This quiz has expired and is no longer available.');
        }

        $request->validate([
            'token' => 'required|string|size:8'
        ]);

        $inputToken = strtoupper(trim($request->token));

        $token = AssessmentToken::where('assessment_id', $assessment->id)
            ->where('token', $inputToken)
            ->where('status', 'active')
            ->first();

        if (!$token) {
            return back()->with('error', 'Invalid or expired token.');
        }

        // Store token in session
        Session::put('assessment_token_id', $token->id);
        Session::put('assessment_start_time', now());

        return redirect()->route('student.assessment.take', $uniqueUrl);
    }

    /**
     * Show assessment questions
     */
    public function takeAssessment($uniqueUrl)
    {
        $assessment = Assessment::where('unique_url', $uniqueUrl)
            ->where('is_quiz', true)
            ->firstOrFail();

        // Check if quiz has expired
        if ($assessment->isExpired()) {
            return redirect()->route('student.assessment.access', $uniqueUrl)
                ->with('error', 'This quiz has expired and is no longer available.');
        }

        $tokenId = Session::get('assessment_token_id');
        if (!$tokenId) {
            return redirect()->route('student.assessment.access', $uniqueUrl);
        }

        $token = AssessmentToken::findOrFail($tokenId);
        if ($token->isUsed()) {
            return redirect()->route('student.assessment.access', $uniqueUrl);
        }

        $questions = $assessment->questions()->orderBy('order')->get();

        return view('student.take-assessment', compact('assessment', 'questions', 'token'));
    }

    /**
     * Submit assessment answers
     */
    public function submitAssessment(Request $request, $uniqueUrl)
    {
        $assessment = Assessment::where('unique_url', $uniqueUrl)
            ->where('is_quiz', true)
            ->firstOrFail();

        // Check if quiz has expired
        if ($assessment->isExpired()) {
            return redirect()->route('student.assessment.access', $uniqueUrl)
                ->with('error', 'This quiz has expired and is no longer available.');
        }

        $tokenId = Session::get('assessment_token_id');
        if (!$tokenId) {
            return redirect()->route('student.assessment.access', $uniqueUrl);
        }

        $token = AssessmentToken::findOrFail($tokenId);
        if ($token->isUsed()) {
            return redirect()->route('student.assessment.access', $uniqueUrl);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string'
        ]);

        $questions = $assessment->questions()->orderBy('order')->get();
        $answers = $request->answers;
        
        $score = 0;
        $totalPoints = $questions->sum('points');
        
        foreach ($questions as $question) {
            if (isset($answers[$question->id]) && $question->isCorrectAnswer($answers[$question->id])) {
                $score += $question->points;
            }
        }

        // Calculate percentage score
        $percentageScore = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;

        // Create or update AssessmentScore
        $assessmentScore = AssessmentScore::updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'student_id' => $token->student_id,
            ],
            [
                'score' => $score,
                'percentage_score' => $percentageScore,
                'is_late' => $assessment->hasDueDate() && now()->isAfter($assessment->due_date),
                'submitted_at' => now(),
            ]
        );

        // Mark token as used
        $token->markAsUsed();

        // Clear session
        Session::forget(['assessment_token_id', 'assessment_start_time']);

        return redirect()->route('student.assessment.result', $uniqueUrl)
            ->with('success', 'Assessment submitted successfully!');
    }

    /**
     * Show assessment result
     */
    public function showResult($uniqueUrl)
    {
        $assessment = Assessment::where('unique_url', $uniqueUrl)
            ->where('is_quiz', true)
            ->firstOrFail();

        // Check if quiz has expired (but allow viewing results)
        if ($assessment->isExpired()) {
            // Still allow viewing results for expired quizzes
        }

        // Get the most recent score for this assessment from the last 24 hours
        // This allows students to see their results even after session expires
        $score = AssessmentScore::where('assessment_id', $assessment->id)
            ->where('submitted_at', '>=', now()->subDay())
            ->orderBy('submitted_at', 'desc')
            ->first();

        if (!$score) {
            return redirect()->route('student.assessment.access', $uniqueUrl)
                ->with('error', 'No recent assessment results found. Please take the assessment first.');
        }

        return view('student.assessment-result', compact('assessment', 'score'));
    }
}
