<?php

namespace App\Http\Controllers;

use App\Services\MLPredictionService;
use App\Services\StudentMetricsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MLPredictionController extends Controller
{
    protected $mlService;
    protected $metricsService;

    public function __construct(MLPredictionService $mlService, StudentMetricsService $metricsService)
    {
        $this->mlService = $mlService;
        $this->metricsService = $metricsService;
    }

    /**
     * Get risk predictions for a student
     */
    public function getStudentRiskPredictions(Request $request): JsonResponse
    {
        $request->validate([
            'avg_score_pct' => 'required|numeric|min:0|max:100',
            'variation_score_pct' => 'required|numeric|min:0|max:100',
            'late_submission_pct' => 'required|numeric|min:0|max:100',
            'missed_submission_pct' => 'required|numeric|min:0|max:100',
        ]);

        $studentData = $request->only([
            'avg_score_pct',
            'variation_score_pct',
            'late_submission_pct',
            'missed_submission_pct'
        ]);

        $predictions = $this->mlService->getRiskPredictions($studentData);

        return response()->json($predictions);
    }

    /**
     * Get risk predictions for multiple students
     */
    public function getBulkRiskPredictions(Request $request): JsonResponse
    {
        $request->validate([
            'students' => 'required|array',
            'students.*.avg_score_pct' => 'required|numeric|min:0|max:100',
            'students.*.variation_score_pct' => 'required|numeric|min:0|max:100',
            'students.*.late_submission_pct' => 'required|numeric|min:0|max:100',
            'students.*.missed_submission_pct' => 'required|numeric|min:0|max:100',
        ]);

        $results = [];
        foreach ($request->students as $index => $studentData) {
            $predictions = $this->mlService->getRiskPredictions($studentData);
            $results[$index] = $predictions;
        }

        return response()->json([
            'success' => true,
            'predictions' => $results
        ]);
    }

    /**
     * Check ML API health
     */
    public function healthCheck(): JsonResponse
    {
        $isHealthy = $this->mlService->isHealthy();
        
        return response()->json([
            'success' => $isHealthy,
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'service' => 'GRAIL ML Prediction Service'
        ]);
    }

    /**
     * Get ML API information
     */
    public function getApiInfo(): JsonResponse
    {
        $info = $this->mlService->getApiInfo();
        
        if ($info) {
            return response()->json($info);
        }

        return response()->json([
            'success' => false,
            'error' => 'Unable to fetch API information'
        ], 503);
    }

    /**
     * Get detailed student metrics
     */
    public function getStudentMetrics($studentId, $classSectionId): JsonResponse
    {
        try {
            $breakdown = $this->metricsService->getDetailedBreakdown($studentId, $classSectionId);
            
            if ($breakdown) {
                return response()->json([
                    'success' => true,
                    'data' => $breakdown
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Student not found or no data available'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get student metrics: ' . $e->getMessage()
            ], 500);
        }
    }
} 