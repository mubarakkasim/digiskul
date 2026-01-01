<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Duty;
use Illuminate\Http\Request;

class DutyController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $query = Duty::forSchool($schoolId)->with('teacher');

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        return response()->json([
            'data' => $query->orderBy('date')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'activity' => 'required|string',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ]);

        $data = $request->all();
        $data['school_id'] = $request->user()->school_id;

        $duty = Duty::create($data);

        return response()->json([
            'message' => 'Duty assigned successfully',
            'data' => $duty->load('teacher')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        $duty = Duty::forSchool($schoolId)->findOrFail($id);

        $request->validate([
            'teacher_id' => 'sometimes|exists:users,id',
            'date' => 'sometimes|date',
            'activity' => 'sometimes|string',
            'description' => 'sometimes|nullable|string',
            'start_time' => 'sometimes|nullable|date_format:H:i',
            'end_time' => 'sometimes|nullable|date_format:H:i|after:start_time',
        ]);

        $duty->update($request->all());

        return response()->json([
            'message' => 'Duty updated successfully',
            'data' => $duty->load('teacher')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $schoolId = $request->user()->school_id;
        $duty = Duty::forSchool($schoolId)->findOrFail($id);
        $duty->delete();

        return response()->json(['message' => 'Duty deleted successfully']);
    }
}
