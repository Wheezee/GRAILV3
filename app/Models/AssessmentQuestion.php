<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'type', // multiple_choice, identification, true_false
        'question_text',
        'options', // JSON for multiple choice
        'correct_answer',
        'points',
        'order'
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'decimal:2',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }

    public function isIdentification(): bool
    {
        return $this->type === 'identification';
    }

    public function isTrueFalse(): bool
    {
        return $this->type === 'true_false';
    }

    public function isCorrectAnswer($answer): bool
    {
        if (empty($answer) && empty($this->correct_answer)) {
            return true;
        }
        
        if (empty($answer) || empty($this->correct_answer)) {
            return false;
        }
        
        $studentAnswer = strtolower(trim((string) $answer));
        $correctAnswer = strtolower(trim((string) $this->correct_answer));
        
        if ($this->isTrueFalse()) {
            $studentAnswer = $studentAnswer === 'true' ? 'true' : 'false';
            $correctAnswer = $correctAnswer === 'true' ? 'true' : 'false';
        }
        
        return $studentAnswer === $correctAnswer;
    }
}
