<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\GradeController;
use App\Http\Controllers\Api\V1\ReportsController;
use App\Http\Controllers\Api\V1\ArchiveController;
use App\Http\Controllers\Api\V1\FeesController;
use App\Http\Controllers\Api\V1\PaymentsController;
use App\Http\Controllers\Api\V1\ReportCardsController;
use App\Http\Controllers\Api\V1\ClassController;
use App\Http\Controllers\Api\V1\DebtorsController;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::get('/locale', [\App\Http\Controllers\Api\V1\LocaleController::class, 'getLocale']);
        Route::post('/locale', [\App\Http\Controllers\Api\V1\LocaleController::class, 'setLocale']);

        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        Route::apiResource('students', StudentController::class);
        Route::post('/attendance/bulk', [AttendanceController::class, 'bulk']);
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/grades/bulk', [GradeController::class, 'bulk']);
        Route::get('/grades', [GradeController::class, 'index']);

        Route::post('/sync/bulk', [SyncController::class, 'bulk']);
        Route::get('/sync/status/{device_id}', [SyncController::class, 'status']);

        // Reports
        Route::post('/reports/report-cards', [ReportsController::class, 'reportCards']);
        Route::post('/reports/class-summary', [ReportsController::class, 'classSummary']);
        Route::post('/reports/attendance', [ReportsController::class, 'attendanceReport']);
        Route::post('/reports/grade-analysis', [ReportsController::class, 'gradeAnalysis']);

        // Archive
        Route::get('/archive', [ArchiveController::class, 'index']);
        Route::post('/archive/current-term', [ArchiveController::class, 'archiveCurrentTerm']);
        Route::get('/archive/{id}/download', [ArchiveController::class, 'download']);
        Route::delete('/archive/{id}', [ArchiveController::class, 'destroy']);

        // Fees
        Route::get('/fees', [FeesController::class, 'index']);
        Route::post('/fees', [FeesController::class, 'store']);

        // Payments
        Route::get('/payments', [PaymentsController::class, 'index']);
        Route::post('/payments', [PaymentsController::class, 'store']);
        Route::get('/payments/{id}/receipt', [PaymentsController::class, 'receipt']);

        // Report Cards
        Route::post('/report-cards/generate', [ReportCardsController::class, 'generate']);
        Route::post('/report-cards/generate-ai-comments', [ReportCardsController::class, 'generateAIComments']);

        // Classes
        Route::get('/classes', [ClassController::class, 'index']);

        // Debtors
        Route::get('/debtors', [DebtorsController::class, 'index']);
        Route::post('/debtors/{id}/remind', [DebtorsController::class, 'remind']);
        Route::post('/debtors/remind', [DebtorsController::class, 'remindAll']);
    });
});

