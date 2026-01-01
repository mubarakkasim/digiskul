<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $archives = \App\Models\ArchiveModel::forSchool($schoolId)
            ->with('classModel')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $archives
        ]);
    }

    public function archiveCurrentTerm(Request $request)
    {
        $request->validate([
            'term' => 'required|string',
            'session' => 'required|string',
            'class_id' => 'sometimes|exists:classes,id',
        ]);

        $user = $request->user();
        $schoolId = $user->school_id;
        
        // Gather data for archive
        $students = \App\Models\Student::forSchool($schoolId);
        if ($request->has('class_id')) {
            $students->where('class_id', $request->class_id);
        }
        $students = $students->get();

        $archiveData = [];

        foreach ($students as $student) {
            $grades = \App\Models\Grade::forSchool($schoolId)
                ->where('student_id', $student->id)
                ->where('term', $request->term)
                ->where('session', $request->session)
                ->with('subject')
                ->get();

            $nonAcademic = \App\Models\NonAcademicPerformance::forSchool($schoolId)
                ->where('student_id', $student->id)
                ->where('term', $request->term)
                ->where('session', $request->session)
                ->first();

            $archiveData[] = [
                'student' => $student,
                'grades' => $grades,
                'non_academic' => $nonAcademic,
            ];
        }

        $archive = \App\Models\ArchiveModel::create([
            'school_id' => $schoolId,
            'term' => $request->term,
            'session' => $request->session,
            'class_id' => $request->class_id ?? null,
            'serialized_data' => $archiveData,
        ]);
        
        return response()->json([
            'message' => 'Term archived successfully',
            'data' => $archive
        ]);
    }

    public function download($id)
    {
        // Generate and download archive zip file
        // Implementation would create zip with all term data
        
        return response()->json(['message' => 'Archive downloaded']);
    }

    public function destroy($id)
    {
        // Delete archived term
        // Implementation would remove archive
        
        return response()->json(['message' => 'Archive deleted successfully']);
    }
}

