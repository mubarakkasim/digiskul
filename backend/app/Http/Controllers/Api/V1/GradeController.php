<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $students = Student::forSchool($schoolId)->get();

        // Get grades for current term
        $grades = Grade::forSchool($schoolId)
            ->where('session', '2024/2025')
            ->where('term', 'First Term')
            ->get()
            ->groupBy(['student_id', 'assessment_type']);

        $students->transform(function ($student) use ($grades) {
            $studentGrades = $grades->get($student->id, collect());
            $student->ca1 = $studentGrades->get('ca1')?->first()?->score ?? '';
            $student->ca2 = $studentGrades->get('ca2')?->first()?->score ?? '';
            $student->exam = $studentGrades->get('exam')?->first()?->score ?? '';
            return $student;
        });

        return response()->json(['data' => $students]);
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.assessment_type' => 'required|in:ca1,ca2,exam',
            'grades.*.score' => 'required|numeric|min:0|max:100',
            'grades.*.term' => 'required|string',
            'grades.*.session' => 'required|string',
        ]);

        $schoolId = $request->user()->school_id;

        foreach ($request->grades as $gradeData) {
            Grade::updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'student_id' => $gradeData['student_id'],
                    'assessment_type' => $gradeData['assessment_type'],
                    'term' => $gradeData['term'],
                    'session' => $gradeData['session'],
                ],
                [
                    'subject_id' => 1, // Should be passed or determined by context
                    'score' => $gradeData['score'],
                    'recorded_by' => $request->user()->id,
                    'synced_at' => now(),
                ]
            );
        }

        return response()->json(['message' => 'Grades saved successfully']);
    }
}

