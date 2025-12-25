<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'device_id',
        'operation_type',
        'payload',
        'status',
        'server_ts',
    ];

    protected $casts = [
        'payload' => 'array',
        'server_ts' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}

