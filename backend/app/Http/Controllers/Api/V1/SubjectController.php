<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $subjects = Subject::where('school_id', $schoolId)
            ->withCount('classes')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $subjects]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:core,elective',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $subject = Subject::create([
            'school_id' => $request->user()->school_id,
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type ?? 'core',
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Subject created successfully',
            'data' => $subject
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        
        $subject = Subject::where('school_id', $schoolId)
            ->withCount('classes')
            ->findOrFail($id);

        return response()->json(['data' => $subject]);
    }

    public function update(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        
        $subject = Subject::where('school_id', $schoolId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:core,elective',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $subject->update([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type ?? 'core',
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Subject updated successfully',
            'data' => $subject
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        
        $subject = Subject::where('school_id', $schoolId)->findOrFail($id);

        // Check if subject is being used in timetable or grades
        // For now, just delete
        $subject->delete();

        return response()->json([
            'message' => 'Subject deleted successfully'
        ]);
    }
}
