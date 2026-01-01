<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveModel extends Model
{
    use HasFactory;

    protected $table = 'archives';

    protected $fillable = [
        'school_id',
        'term',
        'session',
        'class_id',
        'serialized_data',
        'storage_path',
    ];

    protected $casts = [
        'serialized_data' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
