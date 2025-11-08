<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentFee;
use App\Models\FeeTemplate;
use Illuminate\Http\Request;

class FeesController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $fees = StudentFee::forSchool($schoolId)
            ->with(['student', 'feeTemplate'])
            ->get();

        $summary = [
            'total' => $fees->sum('total_due'),
            'paid' => $fees->sum('total_due') - $fees->sum('balance'),
            'outstanding' => $fees->sum('balance'),
            'debtor_count' => $fees->where('balance', '>', 0)->count(),
        ];

        return response()->json([
            'data' => $fees,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_template_id' => 'required|exists:fee_templates,id',
        ]);

        $schoolId = $request->user()->school_id;
        $template = FeeTemplate::findOrFail($request->fee_template_id);

        $fee = StudentFee::create([
            'school_id' => $schoolId,
            'student_id' => $request->student_id,
            'fee_template_id' => $request->fee_template_id,
            'total_due' => $template->total_amount,
            'balance' => $template->total_amount,
        ]);

        return response()->json(['data' => $fee], 201);
    }
}

