<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Models\Category;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::post('/reports', [ReportController::class, 'store']);

    Route::apiResource('reports', ReportController::class);
    Route::get('/my-reports', [ReportController::class, 'myReports']);
    Route::get('/notifications', [NotificationController::class, 'index']);

});

Route::get('/categories', function () {
    return Category::all();
});



