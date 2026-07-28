<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/email/resend-verification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:3,1');

// 2. المسارات المحمية بـ Sanctum (لكل المستخدمين)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::middleware('CheckUser:Admin')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::apiResource('users', UserController::class)->only(['index', 'show']);
        Route::patch('users/{id}/status', [UserController::class, 'changeStatus']);
        Route::apiResource('projects', ProjectController::class)->only(['store','update', 'destroy']);
        Route::apiResource('tasks', TaskController::class)->only(['store','update', 'destroy']);
    });

    Route::middleware('CheckUser:Admin,Employee')->group(function () {
        Route::apiResource('projects', ProjectController::class)->only(['index','show']);
        Route::apiResource('tasks', TaskController::class)->only(['index','show']);
    });

    Route::middleware('CheckUser:Employee')->group(function(){
        Route::patch('tasks/{id}/status', [TaskController::class,'changeStatus']);
    });

    Route::middleware('IsOwnerOrAdmin')->group(function () {
        Route::apiResource('users', UserController::class)->only(['update']);
        Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    });

    // تسجيل الخروج
    Route::post('logout', [AuthController::class, 'logout']);
});