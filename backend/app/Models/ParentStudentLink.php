<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentStudentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'parent_id',
        'student_id',
        'relationship',
        'can_view_grades',
        'can_view_attendance',
        'can_view_fees',
        'active',
    ];

    protected $casts = [
        'can_view_grades' => 'boolean',
        'can_view_attendance' => 'boolean',
        'can_view_fees' => 'boolean',
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
