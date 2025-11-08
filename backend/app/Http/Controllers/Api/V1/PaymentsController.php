<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StudentFee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentsController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $payments = Payment::forSchool($schoolId)
            ->with(['student'])
            ->orderBy('payment_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,bank_transfer,card,mobile_money',
            'payment_date' => 'required|date',
        ]);

        $schoolId = $request->user()->school_id;

        $payment = Payment::create([
            'school_id' => $schoolId,
            'student_id' => $request->student_id,
            'amount' => $request->amount,
            'method' => $request->method,
            'payment_date' => $request->payment_date,
            'recorded_by' => $request->user()->id,
            'receipt_no' => 'RCP-' . strtoupper(Str::random(10)),
            'trx_id' => 'TRX-' . strtoupper(Str::random(12)),
        ]);

        // Update student fee balance
        $studentFee = StudentFee::forSchool($schoolId)
            ->where('student_id', $request->student_id)
            ->first();

        if ($studentFee) {
            $studentFee->balance = max(0, $studentFee->balance - $request->amount);
            $studentFee->save();
        }

        return response()->json(['data' => $payment], 201);
    }

    public function receipt($id)
    {
        $payment = Payment::findOrFail($id);
        
        // Generate PDF receipt
        // Implementation would create PDF using DomPDF
        
        return response()->json(['message' => 'Receipt generated']);
    }
}

