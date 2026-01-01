<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NonAcademicPerformance;
use Illuminate\Http\Request;

class NonAcademicController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $query = NonAcademicPerformance::forSchool($schoolId)->with('student');

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('term')) {
            $query->where('term', $request->term);
        }

        if ($request->has('session')) {
            $query->where('session', $request->session);
        }

        return response()->json([
            'data' => $query->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string',
            'session' => 'required|string',
            'leadership' => 'integer|min:1|max:5',
            'honesty' => 'integer|min:1|max:5',
            'cooperation' => 'integer|min:1|max:5',
            'punctuality' => 'integer|min:1|max:5',
            'neatness' => 'integer|min:1|max:5',
            'teacher_comment' => 'nullable|string',
            'principal_comment' => 'nullable|string',
        ]);

        $schoolId = $request->user()->school_id;
        
        $performance = NonAcademicPerformance::updateOrCreate(
            [
                'school_id' => $schoolId,
                'student_id' => $request->student_id,
                'term' => $request->term,
                'session' => $request->session,
            ],
            $request->all()
        );

        return response()->json([
            'message' => 'Non-academic performance recorded successfully',
            'data' => $performance->load('student')
        ]);
    }

    public function show(Request $request, $student_id)
    {
        $schoolId = $request->user()->school_id;
        $term = $request->query('term');
        $session = $request->query('session');

        $performance = NonAcademicPerformance::forSchool($schoolId)
            ->where('student_id', $student_id)
            ->where('term', $term)
            ->where('session', $session)
            ->first();

        if (!$performance) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        return response()->json(['data' => $performance]);
    }
}
