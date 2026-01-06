<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    /**
     * List teacher assignments
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = TeacherAssignment::with([
            'teacher:id,name,email,role',
            'classModel:id,name,section,level',
            'subject:id,name,code'
        ]);

        // Filter by school
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        } elseif ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by teacher
        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by subject
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by class teacher only
        if ($request->has('class_teacher_only') && $request->boolean('class_teacher_only')) {
            $query->where('is_class_teacher', true);
        }

        // Filter by academic session/term
        if ($request->has('academic_session')) {
            $query->where('academic_session', $request->academic_session);
        }
        if ($request->has('term')) {
            $query->where('term', $request->term);
        }

        // Active only by default
        $query->where('active', true);

        $assignments = $query->orderBy('class_id')->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    /**
     * Create teacher assignment
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'is_class_teacher' => 'sometimes|boolean',
            'academic_session' => 'nullable|string',
            'term' => 'nullable|string',
        ]);

        $validated['school_id'] = $user->isSuperAdmin() 
            ? $request->school_id ?? $request->input('school_id')
            : $user->school_id;
        $validated['active'] = true;

        // If setting as class teacher, remove existing class teacher for this class
        if (isset($validated['is_class_teacher']) && $validated['is_class_teacher']) {
            TeacherAssignment::where('school_id', $validated['school_id'])
                ->where('class_id', $validated['class_id'])
                ->where('is_class_teacher', true)
                ->update(['is_class_teacher' => false]);
        }

        // Check for duplicate
        $existing = TeacherAssignment::where('teacher_id', $validated['teacher_id'])
            ->where('class_id', $validated['class_id'])
            ->where('subject_id', $validated['subject_id'])
            ->first();

        if ($existing) {
            // Update existing
            $existing->update($validated);
            $assignment = $existing;
        } else {
            $assignment = TeacherAssignment::create($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment created successfully',
            'data' => $assignment->load(['teacher', 'classModel', 'subject']),
        ], 201);
    }

    /**
     * Update teacher assignment
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $query = TeacherAssignment::query();
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        $assignment = $query->findOrFail($id);

        $validated = $request->validate([
            'teacher_id' => 'sometimes|exists:users,id',
            'class_id' => 'sometimes|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'is_class_teacher' => 'sometimes|boolean',
            'academic_session' => 'nullable|string',
            'term' => 'nullable|string',
            'active' => 'sometimes|boolean',
        ]);

        // If setting as class teacher, remove existing class teacher for this class
        if (isset($validated['is_class_teacher']) && $validated['is_class_teacher']) {
            $classId = $validated['class_id'] ?? $assignment->class_id;
            TeacherAssignment::where('school_id', $assignment->school_id)
                ->where('class_id', $classId)
                ->where('id', '!=', $id)
                ->where('is_class_teacher', true)
                ->update(['is_class_teacher' => false]);
        }

        $assignment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment updated successfully',
            'data' => $assignment->load(['teacher', 'classModel', 'subject']),
        ]);
    }

    /**
     * Delete teacher assignment
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $query = TeacherAssignment::query();
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        $assignment = $query->findOrFail($id);
        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher assignment deleted successfully',
        ]);
    }
}
