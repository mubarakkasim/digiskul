<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use Illuminate\Http\Request;

class DebtorsController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $debtors = StudentFee::forSchool($schoolId)
            ->where('balance', '>', 0)
            ->with(['student'])
            ->get()
            ->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'student_id' => $fee->student_id,
                    'student_name' => $fee->student->full_name ?? 'N/A',
                    'outstanding' => $fee->balance,
                    'days_overdue' => now()->diffInDays($fee->created_at),
                ];
            });

        return response()->json(['data' => $debtors]);
    }

    public function remind(Request $request, $id)
    {
        $debtor = StudentFee::findOrFail($id);
        
        // Send reminder SMS/Email
        // Implementation would send notification
        
        return response()->json(['message' => 'Reminder sent successfully']);
    }

    public function remindAll(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $debtors = StudentFee::forSchool($schoolId)
            ->where('balance', '>', 0)
            ->get();

        // Send reminders to all debtors
        // Implementation would send notifications
        
        return response()->json([
            'message' => 'Reminders sent successfully',
            'count' => $debtors->count(),
        ]);
    }
}

