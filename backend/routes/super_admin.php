<?php

/**
 * Super Admin API Routes
 * Based on SUPER_ADMIN_PANEL_SPECIFICATION.md
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SuperAdmin\DashboardController;
use App\Http\Controllers\Api\V1\SuperAdmin\LicenseController;
use App\Http\Controllers\Api\V1\SuperAdmin\BackupController;
use App\Http\Controllers\Api\V1\SuperAdmin\UserController;
use App\Http\Controllers\Api\V1\SuperAdmin\SettingsController;
use App\Http\Controllers\Api\V1\SuperAdmin\AnnouncementController;
use App\Http\Controllers\Api\V1\SuperAdmin\HealthController;
use App\Http\Controllers\Api\V1\SuperAdmin\LogController;
use App\Http\Controllers\Api\V1\SchoolController;

Route::prefix('super-admin')->middleware(['auth:sanctum', 'super_admin'])->group(function () {
    
    // ========================================
    // DASHBOARD
    // ========================================
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'recentActivity']);
    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts']);
    Route::get('/dashboard/realtime', [DashboardController::class, 'realTimeMetrics']);

    // ========================================
    // SCHOOL MANAGEMENT
    // ========================================
    Route::get('/schools', [SchoolController::class, 'index']);
    Route::post('/schools', [SchoolController::class, 'store']);
    Route::get('/schools/{id}', [SchoolController::class, 'show']);
    Route::put('/schools/{id}', [SchoolController::class, 'update']);
    Route::delete('/schools/{id}', [SchoolController::class, 'destroy']);
    Route::post('/schools/{id}/suspend', [SchoolController::class, 'suspend']);
    Route::post('/schools/{id}/activate', [SchoolController::class, 'activate']);
    Route::post('/schools/{id}/features', [SchoolController::class, 'updateFeatures']);
    Route::get('/schools/{id}/analytics', [SchoolController::class, 'analytics']);
    Route::get('/schools/{id}/users', [SchoolController::class, 'users']);
    Route::get('/schools/{id}/logs', [SchoolController::class, 'logs']);

    // ========================================
    // LICENSE MANAGEMENT
    // ========================================
    // Plans
    Route::get('/licenses/plans', [LicenseController::class, 'plans']);
    Route::post('/licenses/plans', [LicenseController::class, 'storePlan']);
    Route::get('/licenses/plans/{id}', [LicenseController::class, 'showPlan']);
    Route::put('/licenses/plans/{id}', [LicenseController::class, 'updatePlan']);
    Route::delete('/licenses/plans/{id}', [LicenseController::class, 'destroyPlan']);

    // Subscriptions
    Route::get('/licenses/subscriptions', [LicenseController::class, 'subscriptions']);
    Route::post('/licenses/subscriptions', [LicenseController::class, 'storeSubscription']);
    Route::post('/licenses/subscriptions/{id}/renew', [LicenseController::class, 'renewSubscription']);
    Route::post('/licenses/subscriptions/{id}/cancel', [LicenseController::class, 'cancelSubscription']);
    Route::post('/licenses/subscriptions/{id}/change-plan', [LicenseController::class, 'changePlan']);
    Route::get('/licenses/stats', [LicenseController::class, 'subscriptionStats']);

    // ========================================
    // SETTINGS & CONFIGURATION
    // ========================================
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::get('/settings/{key}', [SettingsController::class, 'show']);
    Route::delete('/settings/{key}', [SettingsController::class, 'destroy']);

    // Features
    Route::get('/settings/features', [SettingsController::class, 'features']);
    Route::post('/settings/features', [SettingsController::class, 'storeFeature']);
    Route::put('/settings/features/{id}', [SettingsController::class, 'updateFeature']);
    Route::post('/settings/features/{id}/toggle', [SettingsController::class, 'toggleFeature']);
    Route::delete('/settings/features/{id}', [SettingsController::class, 'destroyFeature']);

    // Integrations
    Route::get('/settings/integrations', [SettingsController::class, 'integrations']);
    Route::post('/settings/integrations/test', [SettingsController::class, 'testIntegration']);

    // Cache
    Route::post('/settings/cache/clear', [SettingsController::class, 'clearCache']);

    // ========================================
    // USER MANAGEMENT (CROSS-TENANT)
    // ========================================
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/stats', [UserController::class, 'stats']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{id}/activate', [UserController::class, 'activate']);
    Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/users/{id}/transfer', [UserController::class, 'transfer']);
    Route::post('/users/{id}/force-logout', [UserController::class, 'forceLogout']);

    // Impersonation
    Route::post('/users/{id}/impersonate', [UserController::class, 'impersonate']);
    Route::post('/impersonation/{logId}/end', [UserController::class, 'endImpersonation']);
    Route::get('/impersonation/history', [UserController::class, 'impersonationHistory']);

    // ========================================
    // BACKUPS
    // ========================================
    Route::get('/backups', [BackupController::class, 'index']);
    Route::post('/backups', [BackupController::class, 'store']);
    Route::get('/backups/stats', [BackupController::class, 'stats']);
    Route::get('/backups/schedule', [BackupController::class, 'schedule']);
    Route::put('/backups/schedule', [BackupController::class, 'updateSchedule']);
    Route::get('/backups/{id}', [BackupController::class, 'show']);
    Route::get('/backups/{id}/download', [BackupController::class, 'download']);
    Route::post('/backups/{id}/restore', [BackupController::class, 'restore']);
    Route::delete('/backups/{id}', [BackupController::class, 'destroy']);

    // ========================================
    // SYSTEM HEALTH
    // ========================================
    Route::get('/health', [HealthController::class, 'index']);
    Route::get('/health/metrics', [HealthController::class, 'metrics']);
    Route::get('/health/realtime', [HealthController::class, 'realtime']);
    Route::get('/health/api', [HealthController::class, 'apiMetrics']);
    Route::post('/health/cleanup', [HealthController::class, 'cleanup']);

    // ========================================
    // LOGS & AUDIT
    // ========================================
    Route::get('/logs/activity', [LogController::class, 'activity']);
    Route::get('/logs/security', [LogController::class, 'security']);
    Route::get('/logs/system', [LogController::class, 'system']);
    Route::get('/logs/stats', [LogController::class, 'stats']);
    Route::get('/logs/actions', [LogController::class, 'actionTypes']);
    Route::get('/logs/{id}', [LogController::class, 'show']);
    Route::post('/logs/export', [LogController::class, 'export']);

    // ========================================
    // SYSTEM ANNOUNCEMENTS
    // ========================================
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::get('/announcements/active', [AnnouncementController::class, 'active']);
    Route::get('/announcements/login', [AnnouncementController::class, 'loginAnnouncements']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);
    Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::post('/announcements/{id}/publish', [AnnouncementController::class, 'publish']);
    Route::post('/announcements/{id}/unpublish', [AnnouncementController::class, 'unpublish']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);

    // ========================================
    // ANALYTICS
    // ========================================
    Route::get('/analytics/platform', [DashboardController::class, 'stats']);
    Route::get('/analytics/schools', [SchoolController::class, 'systemAnalytics']);

});
