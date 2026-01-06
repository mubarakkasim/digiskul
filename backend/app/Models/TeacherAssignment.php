<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'teacher_id',
        'class_id',
        'subject_id',
        'is_class_teacher',
        'academic_session',
        'term',
        'active',
    ];

    protected $casts = [
        'is_class_teacher' => 'boolean',
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeClassTeachers($query)
    {
        return $query->where('is_class_teacher', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
