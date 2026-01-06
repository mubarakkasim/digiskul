<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Timetable;
use App\Models\Duty;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get role-specific dashboard stats
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        return match($role) {
            'super_admin' => $this->superAdminStats($request),
            'school_admin' => $this->schoolAdminStats($request),
            'teacher' => $this->teacherStats($request),
            'class_teacher' => $this->classTeacherStats($request),
            'student' => $this->studentStats($request),
            'parent' => $this->parentStats($request),
            'bursar' => $this->bursarStats($request),
            default => $this->defaultStats($request),
        };
    }

    /**
     * Super Admin Dashboard - System-wide overview
     */
    protected function superAdminStats(Request $request)
    {
        $stats = [
            'totalSchools' => School::count(),
            'activeSchools' => School::where('active', true)->count(),
            'totalUsers' => User::count(),
            'totalStudents' => Student::count(),
            'schoolsByPlan' => School::selectRaw('subscription_plan, count(*) as count')
                ->groupBy('subscription_plan')
                ->pluck('count', 'subscription_plan'),
            'recentSchools' => School::latest()->take(5)->get(['id', 'name', 'subdomain', 'active', 'created_at']),
            'expiringLicenses' => School::where('license_valid_until', '<=', now()->addDays(30))
                ->where('license_valid_until', '>', now())
                ->count(),
            'systemHealth' => [
                'status' => 'operational',
                'uptime' => '99.9%',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'role' => 'super_admin',
        ]);
    }

    /**
     * School Admin Dashboard - School-wide overview
     */
    protected function schoolAdminStats(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $today = Carbon::today();

        $stats = [
            'totalStudents' => Student::where('school_id', $schoolId)->count(),
            'totalTeachers' => User::where('school_id', $schoolId)
                ->whereIn('role', ['teacher', 'class_teacher'])
                ->count(),
            'totalClasses' => \App\Models\ClassModel::where('school_id', $schoolId)->count(),
            'attendanceToday' => $this->calculateAttendancePercentage($schoolId, $today),
            'pendingAssessments' => $this->countPendingAssessments($schoolId),
            'todayPeriods' => $this->getTodaySchedule($schoolId, null),
            'todayDuties' => Duty::where('school_id', $schoolId)
                ->whereDate('date', $today)
                ->with('teacher:id,name')
                ->get(),
            'recentActivities' => \App\Models\ActivityLog::where('school_id', $schoolId)
                ->latest()
                ->take(10)
                ->with('user:id,name')
                ->get(['action', 'entity_type', 'description', 'user_id', 'created_at']),
            'feesSummary' => $this->getFeeSummary($schoolId),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'role' => 'school_admin',
        ]);
    }

    /**
     * Teacher Dashboard - Personal teaching overview
     */
    protected function teacherStats(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;
        $today = Carbon::today();

        // Get assigned classes
        $assignedClassIds = $user->assignedClasses()->pluck('classes.id');
        
        $stats = [
            'totalStudents' => Student::whereIn('class_id', $assignedClassIds)->count(),
            'totalClasses' => $assignedClassIds->count(),
            'attendanceToday' => $this->calculateAttendancePercentage($schoolId, $today, $assignedClassIds),
            'pendingAssessments' => 0, // To be calculated based on assignments
            'todayPeriods' => $this->getTodaySchedule($schoolId, $user->id),
            'todayDuty' => Duty::where('school_id', $schoolId)
                ->where('teacher_id', $user->id)
                ->whereDate('date', $today)
                ->first(),
            'myClasses' => $user->assignedClasses()
                ->select('classes.id', 'classes.name', 'classes.section')
                ->get(),
            'mySubjects' => $user->teacherAssignments()
                ->with('subject:id,name,code')
                ->where('active', true)
                ->get()
                ->pluck('subject')
                ->unique('id')
                ->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'role' => 'teacher',
        ]);
    }

    /**
     * Class Teacher Dashboard - Extended teacher with class overview
     */
    protected function classTeacherStats(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;
        $today = Carbon::today();

        // Get class where user is class teacher
        $myClass = $user->classTeacherOf()->first();

        $baseStats = $this->teacherStats($request)->getData()->data;

        $classStats = [];
        if ($myClass) {
            $classStudents = Student::where('class_id', $myClass->id);
            
            $classStats = [
                'myClassId' => $myClass->id,
                'myClassName' => $myClass->name . ($myClass->section ? " {$myClass->section}" : ''),
                'classStudentCount' => $classStudents->count(),
                'classAttendanceToday' => $this->calculateClassAttendance($schoolId, $myClass->id, $today),
                'classPerformanceAverage' => $this->calculateClassPerformance($schoolId, $myClass->id),
                'pendingReportCards' => 0, // Count students without approved report cards
            ];
        }

        return response()->json([
            'success' => true,
            'data' => array_merge((array)$baseStats, $classStats),
            'role' => 'class_teacher',
        ]);
    }

    /**
     * Student Dashboard - Personal academic overview
     */
    protected function studentStats(Request $request)
    {
        $user = $request->user();
        
        // Get student profile
        $student = $user->studentProfile;
        
        if (!$student) {
            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'No student profile linked to this account',
                ],
                'role' => 'student',
            ]);
        }

        $schoolId = $user->school_id;
        $today = Carbon::today();

        $stats = [
            'profile' => [
                'name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'class' => $student->classModel->name ?? 'N/A',
            ],
            'attendance' => [
                'percentage' => $this->calculateStudentAttendance($student->id),
                'present_days' => Attendance::where('student_id', $student->id)
                    ->where('status', 'present')
                    ->count(),
                'absent_days' => Attendance::where('student_id', $student->id)
                    ->where('status', 'absent')
                    ->count(),
            ],
            'performance' => [
                'average' => $this->calculateStudentPerformance($student->id),
                'rank' => null, // Calculate rank in class
            ],
            'todaySchedule' => $this->getStudentSchedule($student->class_id),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'role' => 'student',
        ]);
    }

    /**
     * Parent Dashboard - Children overview
     */
    protected function parentStats(Request $request)
    {
        $user = $request->user();
        
        $children = $user->linkedStudents()->with(['classModel:id,name'])->get();

        $childrenStats = $children->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'admission_no' => $student->admission_no,
                'class' => $student->classModel->name ?? 'N/A',
                'attendance_percentage' => $this->calculateStudentAttendance($student->id),
                'performance_average' => $this->calculateStudentPerformance($student->id),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'children' => $childrenStats,
                'totalChildren' => $children->count(),
            ],
            'role' => 'parent',
        ]);
    }

    /**
     * Bursar Dashboard - Financial overview
     */
    protected function bursarStats(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $stats = [
            'totalStudents' => Student::where('school_id', $schoolId)->count(),
            'feesSummary' => $this->getFeeSummary($schoolId),
            'recentPayments' => \App\Models\Payment::where('school_id', $schoolId)
                ->latest()
                ->take(10)
                ->with('student:id,full_name,admission_no')
                ->get(),
            'debtorsCount' => \App\Models\StudentFee::where('school_id', $schoolId)
                ->where('balance', '>', 0)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'role' => 'bursar',
        ]);
    }

    /**
     * Default stats for other roles
     */
    protected function defaultStats(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'totalStudents' => 0,
                'attendanceToday' => 0,
                'pendingAssessments' => 0,
            ],
            'role' => $request->user()->role,
        ]);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function calculateAttendancePercentage($schoolId, $date, $classIds = null)
    {
        $query = Attendance::where('school_id', $schoolId)->whereDate('date', $date);
        
        if ($classIds) {
            $query->whereIn('class_id', $classIds);
        }

        $total = $query->count();
        $present = (clone $query)->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100) : 0;
    }

    protected function calculateClassAttendance($schoolId, $classId, $date)
    {
        $total = Attendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereDate('date', $date)
            ->count();
        
        $present = Attendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereDate('date', $date)
            ->where('status', 'present')
            ->count();

        return $total > 0 ? round(($present / $total) * 100) : 0;
    }

    protected function calculateStudentAttendance($studentId)
    {
        $total = Attendance::where('student_id', $studentId)->count();
        $present = Attendance::where('student_id', $studentId)->where('status', 'present')->count();

        return $total > 0 ? round(($present / $total) * 100) : 0;
    }

    protected function calculateClassPerformance($schoolId, $classId)
    {
        $studentIds = Student::where('class_id', $classId)->pluck('id');
        
        $average = Grade::whereIn('student_id', $studentIds)->avg('score');

        return $average ? round($average, 1) : 0;
    }

    protected function calculateStudentPerformance($studentId)
    {
        $average = Grade::where('student_id', $studentId)->avg('score');

        return $average ? round($average, 1) : 0;
    }

    protected function countPendingAssessments($schoolId)
    {
        // Count classes that haven't had recent grade entries
        return 0; // Implement based on your grading schedule
    }

    protected function getTodaySchedule($schoolId, $teacherId = null)
    {
        $dayOfWeek = strtolower(Carbon::today()->format('l'));

        $query = Timetable::where('school_id', $schoolId)
            ->where('day', $dayOfWeek)
            ->with(['classModel:id,name', 'subject:id,name', 'teacher:id,name']);

        if ($teacherId) {
            $query->where('teacher_id', $teacherId);
        }

        return $query->orderBy('period')->get();
    }

    protected function getStudentSchedule($classId)
    {
        $dayOfWeek = strtolower(Carbon::today()->format('l'));

        return Timetable::where('class_id', $classId)
            ->where('day', $dayOfWeek)
            ->with(['subject:id,name', 'teacher:id,name'])
            ->orderBy('period')
            ->get();
    }

    protected function getFeeSummary($schoolId)
    {
        $totalDue = \App\Models\StudentFee::where('school_id', $schoolId)->sum('total_due');
        $totalPaid = \App\Models\Payment::where('school_id', $schoolId)->sum('amount');
        $totalBalance = \App\Models\StudentFee::where('school_id', $schoolId)->sum('balance');

        return [
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'collection_rate' => $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0,
        ];
    }
}
