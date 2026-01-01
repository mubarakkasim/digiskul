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
        $user = $request->user();
        $schoolId = $user->school_id;

        $totalStudents = Student::forSchool($schoolId)->count();
        
        $todayAttendance = Attendance::forSchool($schoolId)
            ->where('date', today())
            ->get();
        
        $presentCount = $todayAttendance->where('status', 'present')->count();
        $totalAttendance = $todayAttendance->count();
        $attendanceToday = $totalAttendance > 0 
            ? round(($presentCount / $totalAttendance) * 100) 
            : 0;

        // Fetch today's periods from timetable for this teacher
        $dayName = strtolower(today()->format('l'));
        $todayPeriods = \App\Models\Timetable::forSchool($schoolId)
            ->where('teacher_id', $user->id)
            ->where('day', $dayName)
            ->with(['classModel', 'subject'])
            ->orderBy('period')
            ->get();

        // Fetch today's duty for this teacher
        $todayDuty = \App\Models\Duty::forSchool($schoolId)
            ->where('teacher_id', $user->id)
            ->where('date', today())
            ->first();

        // Mock pending assessments
        $pendingAssessments = 3;

        return response()->json([
            'data' => [
                'totalStudents' => $totalStudents,
                'attendanceToday' => $attendanceToday,
                'pendingAssessments' => $pendingAssessments,
                'todayPeriods' => $todayPeriods,
                'todayDuty' => $todayDuty,
            ],
        ]);
    }
}

