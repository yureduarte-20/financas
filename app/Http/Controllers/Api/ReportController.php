<?php

namespace App\Http\Controllers\Api;

use App\Actions\Report\GenerateReportAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Generate financial report.
     *
     * @param Request $request
     * @param GenerateReportAction $action
     * @return JsonResponse
     */
    public function __invoke(Request $request, GenerateReportAction $action): JsonResponse
    {
        $reportData = $action->execute($request->query());

        return response()->json([
            'data' => $reportData,
        ], 200);
    }
}
