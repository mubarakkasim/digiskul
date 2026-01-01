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
        $student = Student::forSchool($schoolId)->with(['classModel'])->findOrFail($request->student_id);

        // Fetch grades
        $grades = Grade::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->where('term', $request->term)
            ->where('session', $request->session)
            ->with(['subject'])
            ->get();

        // Fetch attendance stats for the term
        $attendanceStats = \App\Models\Attendance::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->where('session', $request->session) // Term filtering would be better with dates
            ->select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalDays = $attendanceStats->sum();
        $daysPresent = $attendanceStats['present'] ?? 0;

        // Fetch non-academic performance
        $nonAcademic = \App\Models\NonAcademicPerformance::forSchool($schoolId)
            ->where('student_id', $student->id)
            ->where('term', $request->term)
            ->where('session', $request->session)
            ->first();

        // Group grades by subject
        $subjectGrades = [];
        foreach ($grades as $grade) {
            $subjectId = $grade->subject_id;
            if (!isset($subjectGrades[$subjectId])) {
                $subjectGrades[$subjectId] = [
                    'name' => $grade->subject->name ?? 'N/A',
                    'ca1' => 0,
                    'ca2' => 0,
                    'exam' => 0,
                    'total' => 0,
                    'grade' => '',
                    'comment' => '',
                ];
            }
            
            $score = (float)$grade->score;
            if ($grade->assessment_type == 'ca1') $subjectGrades[$subjectId]['ca1'] = $score;
            if ($grade->assessment_type == 'ca2') $subjectGrades[$subjectId]['ca2'] = $score;
            if ($grade->assessment_type == 'exam') $subjectGrades[$subjectId]['exam'] = $score;
            
            $subjectGrades[$subjectId]['total'] = $subjectGrades[$subjectId]['ca1'] + $subjectGrades[$subjectId]['ca2'] + $subjectGrades[$subjectId]['exam'];
            
            // Basic grading logic
            $total = $subjectGrades[$subjectId]['total'];
            if ($total >= 75) $subjectGrades[$subjectId]['grade'] = 'A1';
            elseif ($total >= 70) $subjectGrades[$subjectId]['grade'] = 'B2';
            elseif ($total >= 65) $subjectGrades[$subjectId]['grade'] = 'B3';
            elseif ($total >= 60) $subjectGrades[$subjectId]['grade'] = 'C4';
            elseif ($total >= 55) $subjectGrades[$subjectId]['grade'] = 'C5';
            elseif ($total >= 50) $subjectGrades[$subjectId]['grade'] = 'C6';
            elseif ($total >= 45) $subjectGrades[$subjectId]['grade'] = 'D7';
            elseif ($total >= 40) $subjectGrades[$subjectId]['grade'] = 'E8';
            else $subjectGrades[$subjectId]['grade'] = 'F9';
        }

        $reportCardData = [
            'student' => $student,
            'term' => $request->term,
            'session' => $request->session,
            'attendance' => [
                'total_days' => $totalDays,
                'days_present' => $daysPresent,
                'percentage' => $totalDays > 0 ? round(($daysPresent / $totalDays) * 100) : 0,
            ],
            'academic_performance' => array_values($subjectGrades),
            'non_academic' => $nonAcademic,
        ];

        return response()->json(['data' => $reportCardData]);
    }

    public function generateAIComments(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'term' => 'required|string',
            'session' => 'required|string',
        ]);

        $schoolId = $request->user()->school_id;
        
        // Fetch grades for context
        $grades = Grade::forSchool($schoolId)
            ->where('student_id', $request->student_id)
            ->where('term', $request->term)
            ->where('session', $request->session)
            ->get();

        $averageScore = $grades->avg('score') ?? 0;
        $subjectsCount = $grades->unique('subject_id')->count();
        
        // Fetch attendance for context
        $attendanceStats = \App\Models\Attendance::forSchool($schoolId)
            ->where('student_id', $request->student_id)
            ->where('session', $request->session)
            ->select('status', \DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalDays = $attendanceStats->sum();
        $daysPresent = $attendanceStats['present'] ?? 0;
        $attendanceRate = $totalDays > 0 ? ($daysPresent / $totalDays) * 100 : 0;

        $comments = [];

        if ($averageScore >= 80) {
            $comments[] = "An exceptional performance this term. Your dedication to your studies is evident in your excellent grades.";
            $comments[] = "You have shown a remarkable understanding of all subjects. Keep up this high standard.";
        } elseif ($averageScore >= 60) {
            $comments[] = "A good performance overall. You have a solid grasp of most concepts but room for improvement exists in some areas.";
            $comments[] = "Steady progress shown. Focus more on consistent revision to reach the top tier.";
        } else {
            $comments[] = "Fair performance. More effort is required in the coming term to improve your understanding of core subjects.";
            $comments[] = "I encourage you to seek extra help in subjects where you are struggling. Consistent practice is key.";
        }

        if ($attendanceRate < 80) {
            $comments[] = "However, your frequent absences are affecting your performance. Regular attendance is crucial for success.";
        } else {
            $comments[] = "Your regular attendance has contributed positively to your learning experience.";
        }
        
        return response()->json([
            'data' => [
                'comments' => $comments,
                'suggested_comment' => implode(' ', $comments)
            ],
        ]);
    }
}

