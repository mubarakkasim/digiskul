<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Grade;
use Illuminate\Http\Request;

class ReportCardsController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string',
            'session' => 'required|string',
        ]);

        $schoolId = $request->user()->school_id;
        $student = Student::forSchool($schoolId)->findOrFail($request->student_id);

        // Fetch grades and attendance
        $grades = Grade::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->where('term', $request->term)
            ->where('session', $request->session)
            ->with(['subject'])
            ->get();

        // Structure data for report card
        $reportCardData = [
            'student_name' => $student->full_name,
            'class' => $student->classModel->name ?? 'N/A',
            'gender' => $student->meta['gender'] ?? 'N/A',
            'class_position' => 45, // Would be calculated
            'subjects' => $grades->map(function ($grade) {
                return [
                    'id' => $grade->id,
                    'name' => $grade->subject->name ?? 'N/A',
                    'ca1' => $grade->where('assessment_type', 'ca1')->first()->score ?? 0,
                    'ca2' => $grade->where('assessment_type', 'ca2')->first()->score ?? 0,
                    'exam' => $grade->where('assessment_type', 'exam')->first()->score ?? 0,
                    'total' => 0, // Would be calculated
                    'grade' => 'A', // Would be calculated
                    'comment' => $grade->comment ?? 'Good performance',
                ];
            }),
        ];

        return response()->json(['data' => $reportCardData]);
    }

    public function generateAIComments(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string',
            'session' => 'required|string',
            'options' => 'array',
        ]);

        // Generate AI comments based on student performance
        // This would integrate with an AI service
        
        return response()->json([
            'data' => [
                'comments' => [
                    'Excellent performance overall',
                    'Keep up the good work',
                ],
            ],
        ]);
    }
}

