<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'full_name',
        'admission_no',
        'class_id',
        'parent_info',
        'health_info',
        'previous_school',
        'created_by_admin_id',
        'meta',
    ];

    protected $casts = [
        'parent_info' => 'array',
        'health_info' => 'array',
        'meta' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function fees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}

