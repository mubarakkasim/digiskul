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
use App\Http\Controllers\Api\V1\TimetableController;
use App\Http\Controllers\Api\V1\DutyController;
use App\Http\Controllers\Api\V1\NonAcademicController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\SchoolController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\ActivityLogController;
use App\Http\Controllers\Api\V1\TeacherAssignmentController;

Route::prefix('v1')->group(function () {
    // ========================================
    // PUBLIC ROUTES (No Authentication Required)
    // ========================================
    Route::post('/auth/login', [AuthController::class, 'login']);

    // ========================================
    // PROTECTED ROUTES (Authentication Required)
    // ========================================
    Route::middleware(['auth:sanctum', 'school.access', 'log.activity'])->group(function () {
        
        // --- Auth Routes ---
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // --- Locale ---
        Route::get('/locale', [\App\Http\Controllers\Api\V1\LocaleController::class, 'getLocale']);
        Route::post('/locale', [\App\Http\Controllers\Api\V1\LocaleController::class, 'setLocale']);

        // --- Dashboard (Role-specific stats) ---
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // ========================================
        // SUPER ADMIN ONLY ROUTES
        // ========================================
        Route::middleware(['role:super_admin'])->prefix('admin')->group(function () {
            // School Management
            Route::get('/schools', [SchoolController::class, 'index']);
            Route::post('/schools', [SchoolController::class, 'store']);
            Route::get('/schools/{id}', [SchoolController::class, 'show']);
            Route::put('/schools/{id}', [SchoolController::class, 'update']);
            Route::delete('/schools/{id}', [SchoolController::class, 'destroy']);
            Route::post('/schools/{id}/suspend', [SchoolController::class, 'suspend']);
            Route::post('/schools/{id}/activate', [SchoolController::class, 'activate']);
            Route::post('/schools/{id}/features', [SchoolController::class, 'updateFeatures']);
            Route::get('/schools/{id}/analytics', [SchoolController::class, 'analytics']);

            // System Settings
            Route::get('/system/settings', [SchoolController::class, 'systemSettings']);
            Route::put('/system/settings', [SchoolController::class, 'updateSystemSettings']);
            Route::get('/system/logs', [ActivityLogController::class, 'systemLogs']);
            Route::get('/system/analytics', [SchoolController::class, 'systemAnalytics']);

            // Global User Management
            Route::get('/users', [UserController::class, 'globalIndex']);
        });

        // ========================================
        // SCHOOL ADMIN ROUTES
        // ========================================
        Route::middleware(['role:super_admin,school_admin'])->group(function () {
            // User Management
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::get('/users/{id}', [UserController::class, 'show']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);
            Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);

            // Teacher Assignments
            Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index']);
            Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store']);
            Route::put('/teacher-assignments/{id}', [TeacherAssignmentController::class, 'update']);
            Route::delete('/teacher-assignments/{id}', [TeacherAssignmentController::class, 'destroy']);

            // Class Management (Full CRUD)
            Route::post('/classes', [ClassController::class, 'store']);
            Route::put('/classes/{id}', [ClassController::class, 'update']);
            Route::delete('/classes/{id}', [ClassController::class, 'destroy']);

            // Subject Management (Full CRUD)
            Route::post('/subjects', [SubjectController::class, 'store']);
            Route::put('/subjects/{id}', [SubjectController::class, 'update']);
            Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

            // Student Management (Full CRUD)
            Route::post('/students', [StudentController::class, 'store']);
            Route::put('/students/{id}', [StudentController::class, 'update']);
            Route::delete('/students/{id}', [StudentController::class, 'destroy']);
            Route::post('/students/import', [StudentController::class, 'import']);
            Route::post('/students/export', [StudentController::class, 'export']);

            // Timetable Management
            Route::post('/timetable', [TimetableController::class, 'store']);
            Route::put('/timetable/{id}', [TimetableController::class, 'update']);
            Route::delete('/timetable/{id}', [TimetableController::class, 'destroy']);
            Route::post('/timetable/approve', [TimetableController::class, 'approve']);
            Route::post('/timetable/publish', [TimetableController::class, 'publish']);

            // Duty Roster Management
            Route::post('/duties', [DutyController::class, 'store']);
            Route::put('/duties/{id}', [DutyController::class, 'update']);
            Route::delete('/duties/{id}', [DutyController::class, 'destroy']);

            // Archive Management
            Route::post('/archive/current-term', [ArchiveController::class, 'archiveCurrentTerm']);
            Route::delete('/archive/{id}', [ArchiveController::class, 'destroy']);

            // Settings Management
            Route::get('/settings', [\App\Http\Controllers\Api\V1\SettingsController::class, 'index']);
            Route::put('/settings', [\App\Http\Controllers\Api\V1\SettingsController::class, 'update']);
            Route::put('/settings/terms', [\App\Http\Controllers\Api\V1\SettingsController::class, 'updateTerms']);

            // Announcements Management
            Route::post('/announcements', [AnnouncementController::class, 'store']);
            Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
            Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);
            Route::post('/announcements/{id}/publish', [AnnouncementController::class, 'publish']);

            // Activity Logs (School Level)
            Route::get('/activity-logs', [ActivityLogController::class, 'index']);

            // Report Card Approval
            Route::post('/report-cards/approve', [ReportCardsController::class, 'approve']);
        });

        // ========================================
        // TEACHER & ABOVE ROUTES (Including Class Teachers)
        // ========================================
        Route::middleware(['role:super_admin,school_admin,teacher,class_teacher'])->group(function () {
            // Attendance
            Route::post('/attendance/bulk', [AttendanceController::class, 'bulk']);
            Route::put('/attendance/{id}', [AttendanceController::class, 'update']);

            // Grades
            Route::post('/grades/bulk', [GradeController::class, 'bulk']);
            Route::put('/grades/{id}', [GradeController::class, 'update']);

            // Report Card Comments
            Route::post('/report-cards/generate-ai-comments', [ReportCardsController::class, 'generateAIComments']);
            Route::post('/report-cards/add-comment', [ReportCardsController::class, 'addComment']);

            // Non-Academic Performance
            Route::post('/non-academic', [NonAcademicController::class, 'store']);
            Route::put('/non-academic/{id}', [NonAcademicController::class, 'update']);
        });

        // ========================================
        // CLASS TEACHER SPECIFIC ROUTES
        // ========================================
        Route::middleware(['role:super_admin,school_admin,class_teacher'])->group(function () {
            // Grade Approval (for their class)
            Route::post('/grades/approve', [GradeController::class, 'approve']);

            // Report Card Generation (for their class)
            Route::post('/report-cards/generate', [ReportCardsController::class, 'generate']);

            // Class Reports
            Route::post('/reports/class-summary', [ReportsController::class, 'classSummary']);
        });

        // ========================================
        // BURSAR / ACCOUNTANT ROUTES
        // ========================================
        Route::middleware(['role:super_admin,school_admin,bursar'])->group(function () {
            // Fees Management
            Route::get('/fees', [FeesController::class, 'index']);
            Route::post('/fees', [FeesController::class, 'store']);
            Route::put('/fees/{id}', [FeesController::class, 'update']);
            Route::delete('/fees/{id}', [FeesController::class, 'destroy']);

            // Payments
            Route::get('/payments', [PaymentsController::class, 'index']);
            Route::post('/payments', [PaymentsController::class, 'store']);
            Route::put('/payments/{id}', [PaymentsController::class, 'update']);
            Route::delete('/payments/{id}', [PaymentsController::class, 'destroy']);
            Route::get('/payments/{id}/receipt', [PaymentsController::class, 'receipt']);
            Route::post('/payments/export', [PaymentsController::class, 'export']);

            // Debtors
            Route::get('/debtors', [DebtorsController::class, 'index']);
            Route::post('/debtors/{id}/remind', [DebtorsController::class, 'remind']);
            Route::post('/debtors/remind', [DebtorsController::class, 'remindAll']);
        });

        // ========================================
        // READ-ONLY ROUTES (All Authenticated Users)
        // ========================================
        
        // Classes (Read)
        Route::get('/classes', [ClassController::class, 'index']);
        Route::get('/classes/{id}', [ClassController::class, 'show']);

        // Subjects (Read)
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/subjects/{id}', [SubjectController::class, 'show']);

        // Students (Read - filtered by role)
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/{id}', [StudentController::class, 'show']);

        // Attendance (Read - filtered by role)
        Route::get('/attendance', [AttendanceController::class, 'index']);

        // Grades (Read - filtered by role)
        Route::get('/grades', [GradeController::class, 'index']);

        // Timetable (Read - filtered by role)
        Route::get('/timetable', [TimetableController::class, 'index']);

        // Duties (Read - filtered by role)
        Route::get('/duties', [DutyController::class, 'index']);

        // Reports (Read - filtered by role)
        Route::post('/reports/report-cards', [ReportsController::class, 'reportCards']);
        Route::post('/reports/attendance', [ReportsController::class, 'attendanceReport']);
        Route::post('/reports/grade-analysis', [ReportsController::class, 'gradeAnalysis']);

        // Report Cards (Read - filtered by role)
        Route::get('/report-cards', [ReportCardsController::class, 'index']);
        Route::get('/report-cards/{id}/download', [ReportCardsController::class, 'download']);

        // Archive (Read)
        Route::get('/archive', [ArchiveController::class, 'index']);
        Route::get('/archive/{id}/download', [ArchiveController::class, 'download']);

        // Non-Academic Performance (Read - filtered by role)
        Route::get('/non-academic', [NonAcademicController::class, 'index']);
        Route::get('/non-academic/student/{student_id}', [NonAcademicController::class, 'show']);

        // Announcements (Read - filtered by role)
        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);

        // ========================================
        // STUDENT SPECIFIC ROUTES
        // ========================================
        Route::middleware(['role:student'])->prefix('student')->group(function () {
            Route::get('/profile', [StudentController::class, 'myProfile']);
            Route::get('/attendance', [AttendanceController::class, 'myAttendance']);
            Route::get('/grades', [GradeController::class, 'myGrades']);
            Route::get('/report-card', [ReportCardsController::class, 'myReportCard']);
            Route::get('/timetable', [TimetableController::class, 'myTimetable']);
        });

        // ========================================
        // PARENT SPECIFIC ROUTES
        // ========================================
        Route::middleware(['role:parent'])->prefix('parent')->group(function () {
            Route::get('/children', [StudentController::class, 'myChildren']);
            Route::get('/children/{student_id}/attendance', [AttendanceController::class, 'childAttendance']);
            Route::get('/children/{student_id}/grades', [GradeController::class, 'childGrades']);
            Route::get('/children/{student_id}/report-card', [ReportCardsController::class, 'childReportCard']);
            Route::get('/children/{student_id}/fees', [FeesController::class, 'childFees']);
        });

        // Legacy Sync Routes (for offline support)
        Route::post('/sync/bulk', [\App\Http\Controllers\Api\V1\SyncController::class, 'bulk']);
        Route::get('/sync/status/{device_id}', [\App\Http\Controllers\Api\V1\SyncController::class, 'status']);
    });
});
