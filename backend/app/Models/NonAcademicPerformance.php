<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonAcademicPerformance extends Model
{
    use HasFactory;

    protected $table = 'non_academic_performance';

    protected $fillable = [
        'school_id',
        'student_id',
        'term',
        'session',
        'leadership',
        'honesty',
        'cooperation',
        'punctuality',
        'neatness',
        'teacher_comment',
        'principal_comment',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
