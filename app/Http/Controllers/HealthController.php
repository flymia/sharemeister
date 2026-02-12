<?php

namespace App\Http\Controllers;

use App\Services\HealthService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __construct(
        protected HealthService $healthService
    ) {}

    /**
     * Handle the health check request.
     */
    public function __invoke(): JsonResponse
    {
        try {
            $health = $this->healthService->checkSystemHealth();
            return response()->json($health, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'System unhealthy',
                'error'   => config('app.debug') ? $e->getMessage() : 'Service Unavailable',
            ], 503);
        }
    }
}