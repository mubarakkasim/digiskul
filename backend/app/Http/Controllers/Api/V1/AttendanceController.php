<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $date = $request->get('date', today()->format('Y-m-d'));

        // Get students for the school
        $students = Student::forSchool($schoolId)
            ->with(['classModel'])
            ->get();

        // Get existing attendance for the date
        $attendanceRecords = Attendance::forSchool($schoolId)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');

        // Merge students with attendance status
        $students->transform(function ($student) use ($attendanceRecords) {
            $record = $attendanceRecords->get($student->id);
            $student->attendance_status = $record ? $record->status : 'absent';
            return $student;
        });

        return response()->json(['data' => $students]);
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
        ]);

        $schoolId = $request->user()->school_id;
        $date = $request->date;

        DB::transaction(function () use ($schoolId, $date, $request) {
            foreach ($request->attendance as $attendanceData) {
                Attendance::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'student_id' => $attendanceData['student_id'],
                        'date' => $date,
                    ],
                    [
                        'class_id' => Student::find($attendanceData['student_id'])->class_id,
                        'session' => '2024/2025',
                        'status' => $attendanceData['status'],
                        'recorded_by' => $request->user()->id,
                        'synced_at' => now(),
                    ]
                );
            }
        });

        return response()->json(['message' => 'Attendance recorded successfully']);
    }
}

