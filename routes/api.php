<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('')->group(function () {
    // Auth
    Route::post('/auth/login', [ApiController::class, 'login']);
    Route::post('/auth/logout', [ApiController::class, 'logout'])->middleware('auth:sanctum');

    // Events
    Route::get('/events', [ApiController::class, 'listEvents'])->middleware('auth:sanctum');
    Route::post('/events', [ApiController::class, 'createEvent'])->middleware('auth:sanctum');
    Route::get('/events/{id}', [ApiController::class, 'getEvent'])->middleware('auth:sanctum');
    Route::put('/events/{id}', [ApiController::class, 'updateEvent'])->middleware('auth:sanctum');

    // Guests
    Route::get('/guests', [ApiController::class, 'listGuests'])->middleware('auth:sanctum');
    Route::post('/guests', [ApiController::class, 'createGuest'])->middleware('auth:sanctum');
    Route::get('/guests/{id}', [ApiController::class, 'getGuest'])->middleware('auth:sanctum');
    Route::put('/guests/{id}', [ApiController::class, 'updateGuest'])->middleware('auth:sanctum');
    Route::delete('/guests/{id}', [ApiController::class, 'deleteGuest'])->middleware('auth:sanctum');

    // Check-in via QR
    Route::post('/checkin/qr', [ApiController::class, 'checkinByQr'])->middleware('auth:sanctum');

    // Approval Requests
    Route::get('/approval-requests', [ApiController::class, 'listApprovalRequests'])->middleware('auth:sanctum');
    Route::post('/approval-requests', [ApiController::class, 'createApprovalRequest'])->middleware('auth:sanctum');
    Route::get('/approval-requests/{id}', [ApiController::class, 'getApprovalRequest'])->middleware('auth:sanctum');
    Route::post('/approval-requests/{id}/approve', [ApiController::class, 'approveRequest'])->middleware('auth:sanctum');
    Route::post('/approval-requests/{id}/reject', [ApiController::class, 'rejectRequest'])->middleware('auth:sanctum');

    // Dashboard/Stats
    Route::get('/stats', [ApiController::class, 'getStats'])->middleware('auth:sanctum');
});
