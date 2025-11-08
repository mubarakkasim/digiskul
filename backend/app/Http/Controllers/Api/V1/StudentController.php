<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $students = Student::forSchool($schoolId)
            ->with(['classModel'])
            ->paginate($request->get('per_page', 15));

        // Add calculated fields
        $students->getCollection()->transform(function ($student) {
            $student->attendance_rate = $this->calculateAttendanceRate($student);
            $student->average_grade = $this->calculateAverageGrade($student);
            return $student;
        });

        return response()->json([
            'data' => $students->items(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'admission_no' => 'required|string|unique:students',
            'class_id' => 'required|exists:classes,id',
            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'parent_phone' => 'nullable|string',
        ]);

        $student = Student::create([
            'school_id' => $request->user()->school_id,
            'full_name' => $request->full_name,
            'admission_no' => $request->admission_no,
            'class_id' => $request->class_id,
            'parent_info' => [
                'phone' => $request->parent_phone,
            ],
            'meta' => [
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
            ],
            'created_by_admin_id' => $request->user()->id,
        ]);

        return response()->json(['data' => $student], 201);
    }

    public function show(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        
        $student = Student::forSchool($schoolId)->findOrFail($id);
        $student->attendance_rate = $this->calculateAttendanceRate($student);
        $student->average_grade = $this->calculateAverageGrade($student);

        return response()->json(['data' => $student]);
    }

    public function update(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        
        $student = Student::forSchool($schoolId)->findOrFail($id);

        $request->validate([
            'full_name' => 'sometimes|required|string',
            'admission_no' => 'sometimes|required|string|unique:students,admission_no,' . $id,
            'class_id' => 'sometimes|required|exists:classes,id',
        ]);

        $student->update($request->only(['full_name', 'admission_no', 'class_id']));

        return response()->json(['data' => $student]);
    }

    private function calculateAttendanceRate($student)
    {
        // Calculate attendance rate for current term
        $total = $student->attendance()->count();
        $present = $student->attendance()->where('status', 'present')->count();
        
        return $total > 0 ? round(($present / $total) * 100) : 0;
    }

    private function calculateAverageGrade($student)
    {
        // Calculate average grade for current term
        $grades = $student->grades()
            ->where('session', '2024/2025')
            ->where('term', 'First Term')
            ->get();
        
        if ($grades->isEmpty()) {
            return 0;
        }

        return round($grades->avg('score'), 2);
    }
}

