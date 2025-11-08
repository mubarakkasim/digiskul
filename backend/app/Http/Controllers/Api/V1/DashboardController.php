<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $totalStudents = Student::forSchool($schoolId)->count();
        
        $todayAttendance = Attendance::forSchool($schoolId)
            ->where('date', today())
            ->get();
        
        $presentCount = $todayAttendance->where('status', 'present')->count();
        $totalAttendance = $todayAttendance->count();
        $attendanceToday = $totalAttendance > 0 
            ? round(($presentCount / $totalAttendance) * 100) 
            : 0;

        // Mock pending assessments - should be calculated based on actual assessments
        $pendingAssessments = 3;

        return response()->json([
            'data' => [
                'totalStudents' => $totalStudents,
                'attendanceToday' => $attendanceToday,
                'pendingAssessments' => $pendingAssessments,
            ],
        ]);
    }
}

