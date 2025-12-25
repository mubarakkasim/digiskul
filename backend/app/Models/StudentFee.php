<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'student_id',
        'fee_template_id',
        'total_due',
        'scholarships',
        'balance',
    ];

    protected $casts = [
        'total_due' => 'decimal:2',
        'balance' => 'decimal:2',
        'scholarships' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeTemplate()
    {
        return $this->belongsTo(FeeTemplate::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}

