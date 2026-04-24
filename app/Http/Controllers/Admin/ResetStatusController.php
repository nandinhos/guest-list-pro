<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ResetStatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $status = session('reset_database_job', [
            'status' => 'idle',
            'current_step' => 0,
            'steps' => [],
            'error_message' => null,
            'error_trace' => null,
            'updated_at' => null,
        ]);

        return response()->json($status);
    }
}
