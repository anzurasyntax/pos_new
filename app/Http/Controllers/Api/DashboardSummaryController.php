<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\Http\JsonResponse;

class DashboardSummaryController extends Controller
{
    public function __invoke(DashboardMetricsService $metrics): JsonResponse
    {
        return response()->json($metrics->todaySummary());
    }
}
