<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $query = Timetable::forSchool($schoolId)
            ->with(['classModel', 'subject', 'teacher']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->has('day')) {
            $query->where('day', strtolower($request->day));
        }

        return response()->json([
            'data' => $query->orderBy('day')->orderBy('period')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'period' => 'required|integer',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $data = $request->all();
        $data['school_id'] = $request->user()->school_id;

        $timetable = Timetable::create($data);

        return response()->json([
            'message' => 'Timetable entry created successfully',
            'data' => $timetable->load(['classModel', 'subject', 'teacher'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        $timetable = Timetable::forSchool($schoolId)->findOrFail($id);

        $request->validate([
            'class_id' => 'sometimes|exists:classes,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'teacher_id' => 'sometimes|exists:users,id',
            'day' => 'sometimes|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'period' => 'sometimes|integer',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
        ]);

        $timetable->update($request->all());

        return response()->json([
            'message' => 'Timetable updated successfully',
            'data' => $timetable->load(['classModel', 'subject', 'teacher'])
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        $timetable = Timetable::forSchool($schoolId)->findOrFail($id);
        $timetable->delete();

        return response()->json(['message' => 'Timetable entry deleted successfully']);
    }
}
