<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\FinancialReportController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\OreReportController;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', LoginController::class)->middleware('throttle:5,1');
    
    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/me', [LoginController::class, 'me']);
    Route::apiResource('locations', LocationController::class);
    Route::get('/my-locations', function (Request $request) {
        $user = $request->user();
        if ($request->user()->role === 'direktur'){
            return response()->json(Location::all());
        }
        return response()->json($user->locations);
        });
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendances/my-history', [AttendanceController::class, 'myHistory']);
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::apiResource('ore-reports', OreReportController::class);
    Route::post('/ore-reports/{id}/comments', [OreReportController::class, 'addComment']);
    Route::apiResource('financial-reports', FinancialReportController::class);
    });
});