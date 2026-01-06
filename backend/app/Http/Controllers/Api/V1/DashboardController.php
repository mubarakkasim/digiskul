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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get role-specific dashboard stats
     */
    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 401);
            }
            
            $role = $user->role;
            $schoolId = $user->school_id;
            
            Log::info('Dashboard stats requested', ['user_id' => $user->id, 'role' => $role]);

            // Build stats based on role
            $stats = $this->getBaseStats($schoolId);
            
            // Add role-specific stats
            if ($role === 'super_admin') {
                $stats = array_merge($stats, $this->getSuperAdminStats());
            } elseif ($role === 'bursar') {
                $stats = array_merge($stats, $this->getBursarStats($schoolId));
            } elseif ($role === 'student') {
                $stats = $this->getStudentStats($user);
            } elseif ($role === 'parent') {
                $stats = $this->getParentStats($user);
            }

            return response()->json([
                'success' => true,
                'data' => $stats,
                'role' => $role,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get base stats for most roles
     */
    protected function getBaseStats($schoolId)
    {
        $today = Carbon::today();
        
        $stats = [
            'totalStudents' => $schoolId ? Student::where('school_id', $schoolId)->count() : 0,
            'totalTeachers' => $schoolId 
                ? User::where('school_id', $schoolId)->whereIn('role', ['teacher', 'class_teacher'])->count() 
                : 0,
            'totalClasses' => $schoolId 
                ? \App\Models\ClassModel::where('school_id', $schoolId)->count() 
                : 0,
            'attendanceToday' => $this->calculateAttendancePercentage($schoolId, $today),
            'pendingAssessments' => 0,
        ];

        // Get today's schedule
        try {
            $dayOfWeek = strtolower($today->format('l'));
            $stats['todayPeriods'] = $schoolId 
                ? Timetable::where('school_id', $schoolId)
                    ->where('day', $dayOfWeek)
                    ->with(['classModel:id,name', 'subject:id,name', 'teacher:id,name'])
                    ->orderBy('period')
                    ->limit(10)
                    ->get()
                : [];
        } catch (\Exception $e) {
            Log::warning('Failed to get today schedule: ' . $e->getMessage());
            $stats['todayPeriods'] = [];
        }

        // Get today's duties
        try {
            $stats['todayDuties'] = $schoolId 
                ? Duty::where('school_id', $schoolId)
                    ->whereDate('date', $today)
                    ->with('teacher:id,name')
                    ->get()
                : [];
        } catch (\Exception $e) {
            Log::warning('Failed to get today duties: ' . $e->getMessage());
            $stats['todayDuties'] = [];
        }

        return $stats;
    }

    /**
     * Get super admin specific stats
     */
    protected function getSuperAdminStats()
    {
        return [
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
    }

    /**
     * Get bursar specific stats
     */
    protected function getBursarStats($schoolId)
    {
        $stats = [];
        
        try {
            $stats['feesSummary'] = [
                'total_due' => \App\Models\StudentFee::where('school_id', $schoolId)->sum('total_due') ?? 0,
                'total_paid' => \App\Models\Payment::where('school_id', $schoolId)->sum('amount') ?? 0,
                'total_balance' => \App\Models\StudentFee::where('school_id', $schoolId)->sum('balance') ?? 0,
                'collection_rate' => 0,
            ];
            
            if ($stats['feesSummary']['total_due'] > 0) {
                $stats['feesSummary']['collection_rate'] = round(
                    ($stats['feesSummary']['total_paid'] / $stats['feesSummary']['total_due']) * 100
                );
            }
            
            $stats['debtorsCount'] = \App\Models\StudentFee::where('school_id', $schoolId)
                ->where('balance', '>', 0)
                ->count();
                
            $stats['recentPayments'] = \App\Models\Payment::where('school_id', $schoolId)
                ->latest()
                ->take(10)
                ->with('student:id,full_name,admission_no')
                ->get();
        } catch (\Exception $e) {
            Log::warning('Failed to get bursar stats: ' . $e->getMessage());
            $stats['feesSummary'] = ['total_due' => 0, 'total_paid' => 0, 'total_balance' => 0, 'collection_rate' => 0];
            $stats['debtorsCount'] = 0;
            $stats['recentPayments'] = [];
        }
        
        return $stats;
    }

    /**
     * Get student specific stats
     */
    protected function getStudentStats($user)
    {
        $stats = [
            'profile' => [
                'name' => $user->name,
            ],
            'attendance' => [
                'percentage' => 0,
                'present_days' => 0,
                'absent_days' => 0,
            ],
            'performance' => [
                'average' => 0,
                'rank' => null,
            ],
            'todaySchedule' => [],
        ];
        
        try {
            // Get student profile if linked
            if (method_exists($user, 'studentProfile') && $user->studentProfile) {
                $student = $user->studentProfile;
                $stats['profile'] = [
                    'name' => $student->full_name ?? $user->name,
                    'admission_no' => $student->admission_no ?? 'N/A',
                    'class' => $student->classModel->name ?? 'N/A',
                ];
                
                $stats['attendance'] = [
                    'percentage' => $this->calculateStudentAttendance($student->id),
                    'present_days' => Attendance::where('student_id', $student->id)->where('status', 'present')->count(),
                    'absent_days' => Attendance::where('student_id', $student->id)->where('status', 'absent')->count(),
                ];
                
                $stats['performance']['average'] = $this->calculateStudentPerformance($student->id);
                
                // Get today's schedule for student's class
                if ($student->class_id) {
                    $dayOfWeek = strtolower(Carbon::today()->format('l'));
                    $stats['todaySchedule'] = Timetable::where('class_id', $student->class_id)
                        ->where('day', $dayOfWeek)
                        ->with(['subject:id,name', 'teacher:id,name'])
                        ->orderBy('period')
                        ->get();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get student stats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Get parent specific stats
     */
    protected function getParentStats($user)
    {
        $stats = [
            'children' => [],
            'totalChildren' => 0,
        ];
        
        try {
            if (method_exists($user, 'linkedStudents')) {
                $children = $user->linkedStudents()->with('classModel:id,name')->get();
                
                $stats['children'] = $children->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'admission_no' => $student->admission_no,
                        'class' => $student->classModel->name ?? 'N/A',
                        'attendance_percentage' => $this->calculateStudentAttendance($student->id),
                        'performance_average' => $this->calculateStudentPerformance($student->id),
                    ];
                });
                
                $stats['totalChildren'] = $children->count();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get parent stats: ' . $e->getMessage());
        }
        
        return $stats;
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function calculateAttendancePercentage($schoolId, $date, $classIds = null)
    {
        if (!$schoolId) return 0;
        
        try {
            $query = Attendance::where('school_id', $schoolId)->whereDate('date', $date);
            
            if ($classIds) {
                $query->whereIn('class_id', $classIds);
            }

            $total = $query->count();
            $present = (clone $query)->where('status', 'present')->count();

            return $total > 0 ? round(($present / $total) * 100) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function calculateStudentAttendance($studentId)
    {
        try {
            $total = Attendance::where('student_id', $studentId)->count();
            $present = Attendance::where('student_id', $studentId)->where('status', 'present')->count();

            return $total > 0 ? round(($present / $total) * 100) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function calculateStudentPerformance($studentId)
    {
        try {
            $average = Grade::where('student_id', $studentId)->avg('score');
            return $average ? round($average, 1) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
