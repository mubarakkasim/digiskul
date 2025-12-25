<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'components',
        'total_amount',
    ];

    protected $casts = [
        'components' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}

