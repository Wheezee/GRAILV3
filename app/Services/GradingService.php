<?php

namespace App\Services;

class GradingService
{
    /**
     * Calculate grade based on percentage and grading method
     */
    public function calculateGrade($percentage, $method = 'linear', $params = [])
    {
        switch ($method) {
            case 'linear':
                return $this->linearGrading($percentage, $params);
            case 'curved':
                return $this->curvedGrading($percentage, $params);
            case 'pass_fail':
                return $this->passFailGrading($percentage, $params);
            case 'custom':
                return $this->customGrading($percentage, $params);
            default:
                return $percentage; // Return as percentage
        }
    }

    /**
     * Linear grading with configurable parameters
     */
    private function linearGrading($percentage, $params = [])
    {
        $maxScore = $params['max_score'] ?? 95;
        $maxGrade = $params['max_grade'] ?? 100;
        $passingScore = $params['passing_score'] ?? 75;
        $passingGrade = $params['passing_grade'] ?? 3.0;

        // If percentage is higher than max score, scale it down
        if ($percentage > $maxScore) {
            $percentage = $maxScore;
        }

        // Calculate grade using linear interpolation
        if ($percentage >= $passingScore) {
            $grade = $passingGrade - (($percentage - $passingScore) / ($maxScore - $passingScore)) * ($passingGrade - 1.0);
        } else {
            // Below passing score, grade increases linearly to 5.0
            $grade = $passingGrade + (($passingScore - $percentage) / $passingScore) * (5.0 - $passingGrade);
        }

        return round($grade, 2);
    }

    /**
     * Curved grading based on class performance
     */
    private function curvedGrading($percentage, $params = [])
    {
        $maxScore = $params['max_score'] ?? 95;
        $passingScore = $params['passing_score'] ?? 75;
        $passingGrade = $params['passing_grade'] ?? 3.0;

        // Scale percentage to max score
        $scaledPercentage = ($percentage / 100) * $maxScore;

        if ($scaledPercentage >= $passingScore) {
            $grade = 1.0 + (($maxScore - $scaledPercentage) / ($maxScore - $passingScore)) * ($passingGrade - 1.0);
        } else {
            $grade = $passingGrade + (($passingScore - $scaledPercentage) / $passingScore) * (5.0 - $passingGrade);
        }

        return round($grade, 2);
    }

    /**
     * Pass/Fail with grade ranges
     */
    private function passFailGrading($percentage, $params = [])
    {
        $passingScore = $params['passing_score'] ?? 75;
        $passingGrade = $params['passing_grade'] ?? 3.0;

        if ($percentage >= $passingScore) {
            // Pass with grades 1.0 to passing grade
            $grade = 1.0 + (($percentage - $passingScore) / (100 - $passingScore)) * ($passingGrade - 1.0);
        } else {
            // Fail
            $grade = 5.0;
        }

        return round($grade, 2);
    }

    /**
     * Custom grading with user-defined formula
     */
    private function customGrading($percentage, $params = [])
    {
        $maxScore = $params['max_score'] ?? 95;
        $maxGrade = $params['max_grade'] ?? 100;
        $passingScore = $params['passing_score'] ?? 75;
        $passingGrade = $params['passing_grade'] ?? 3.0;
        $formula = $params['formula'] ?? 'linear';

        switch ($formula) {
            case 'inverse_linear':
                // Linear scale: 95% = 1.1, 75% = 3.0
                // Cap percentage at max_score (95%)
                $effectivePercentage = min($percentage, $maxScore);
                
                if ($effectivePercentage >= $passingScore) {
                    // Above passing: linear scale from passing score to max score
                    $grade = $passingGrade - (($effectivePercentage - $passingScore) / ($maxScore - $passingScore)) * ($passingGrade - 1.1);
                } else {
                    // Below passing: linear scale to 5.0
                    $grade = $passingGrade + (($passingScore - $effectivePercentage) / $passingScore) * (5.0 - $passingGrade);
                }
                
                return round($grade, 2);
            
            case 'exponential':
                // Exponential curve
                $normalized = ($percentage - $passingScore) / (100 - $passingScore);
                $grade = $passingGrade - ($normalized * ($passingGrade - 1.0));
                return round(max(1.0, $grade), 2);
            
            case 'step':
                // Step-based grading
                if ($percentage >= 97) return 1.00;
                if ($percentage >= 94) return 1.25;
                if ($percentage >= 91) return 1.50;
                if ($percentage >= 88) return 1.75;
                if ($percentage >= 85) return 2.00;
                if ($percentage >= 82) return 2.25;
                if ($percentage >= 79) return 2.50;
                if ($percentage >= 76) return 2.75;
                if ($percentage >= $passingScore) return $passingGrade;
                return 5.00;
            
            default:
                return $this->linearGrading($percentage, $params);
        }
    }

    /**
     * Get grade color based on grade value and method
     */
    public function getGradeColor($grade, $method = 'linear')
    {
        if ($method === 'percentage') {
            return ''; // No color for percentage
        }

        $numGrade = is_numeric($grade) ? $grade : 0;
        
        if ($numGrade <= 1.0) return 'text-green-600 dark:text-green-400'; // Excellent
        if ($numGrade <= 1.5) return 'text-blue-600 dark:text-blue-400'; // Very Good
        if ($numGrade <= 1.75) return 'text-yellow-600 dark:text-yellow-400'; // Good
        if ($numGrade <= 2.5) return 'text-orange-600 dark:text-orange-400'; // Fair
        if ($numGrade <= 2.75) return 'text-orange-600 dark:text-orange-400'; // Passing
        if ($numGrade <= 3.0) return 'text-red-500 dark:text-red-400'; // Lowest Passing
        return 'text-red-700 dark:text-red-500'; // Failed
    }

    /**
     * Format grade display based on method
     */
    public function formatGrade($grade, $method = 'percentage')
    {
        if ($method === 'percentage') {
            return $grade . '%';
        }

        return number_format($grade, 2);
    }

    /**
     * Get default parameters for each grading method
     */
    public function getDefaultParams($method = 'linear')
    {
        $defaults = [
            'linear' => [
                'max_score' => 95,
                'max_grade' => 100,
                'passing_score' => 75,
                'passing_grade' => 3.0
            ],
            'curved' => [
                'max_score' => 95,
                'passing_score' => 75,
                'passing_grade' => 3.0
            ],
            'pass_fail' => [
                'passing_score' => 75,
                'passing_grade' => 3.0
            ],
            'custom' => [
                'max_score' => 95,
                'max_grade' => 100,
                'passing_score' => 75,
                'passing_grade' => 3.0,
                'formula' => 'inverse_linear'
            ]
        ];

        return $defaults[$method] ?? $defaults['linear'];
    }
} 