<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLPredictionService
{
    private $apiUrl;
    private $timeout;

    public function __construct()
    {
        $this->apiUrl = config('services.ml.api_url', 'http://127.0.0.1:5000');
        $this->timeout = config('services.ml.timeout', 5);
    }

    /**
     * Get risk predictions for a student
     */
    public function getRiskPredictions($studentData)
    {
        try {
            // Prepare the data according to your Flask API requirements
            $payload = [
                'avg_score_pct' => $studentData['avg_score_pct'] ?? 0,
                'variation_score_pct' => $studentData['variation_score_pct'] ?? 0,
                'late_submission_pct' => $studentData['late_submission_pct'] ?? 0,
                'missed_submission_pct' => $studentData['missed_submission_pct'] ?? 0,
            ];

            // Make API call with fallback
            $response = $this->makeApiCall($payload);

            if ($response && $response['success']) {
                return [
                    'success' => true,
                    'has_risks' => $response['has_risks'] ?? false,
                    'risks' => $response['risks'] ?? [],
                    'risk_count' => $response['risk_count'] ?? 0,
                    'input_data' => $response['input_data'] ?? $payload
                ];
            }

            return [
                'success' => false,
                'error' => 'API call failed',
                'has_risks' => false,
                'risks' => [],
                'risk_count' => 0
            ];

        } catch (\Exception $e) {
            Log::error('ML Prediction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Service unavailable',
                'has_risks' => false,
                'risks' => [],
                'risk_count' => 0
            ];
        }
    }

    /**
     * Make API call with fallback
     */
    private function makeApiCall($payload)
    {
        // Try localhost first, short timeout
        try {
            $response = Http::timeout(0.1)
                ->post('http://127.0.0.1:5000/api/predict', $payload);
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning("ML API localhost failed: " . $e->getMessage());
        }

        // Fallback to remote API, longer timeout
        try {
            $response = Http::timeout(2)
                ->post('https://buratizer127.pythonanywhere.com/api/predict', $payload);
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::warning("ML API remote fallback failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if ML API is healthy
     */
    public function isHealthy()
    {
        try {
            $response = Http::timeout(2)->get($this->apiUrl . '/api/health');
            return $response->successful() && $response->json('success') === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get API information
     */
    public function getApiInfo()
    {
        try {
            $response = Http::timeout(3)->get($this->apiUrl . '/api/info');
            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to get API info: ' . $e->getMessage());
        }

        return null;
    }
} 