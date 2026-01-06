<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'created_by',
        'title',
        'content',
        'target_roles',
        'is_global',
        'published_at',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_global' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId)->orWhere('is_global', true);
    }

    public function scopeForRole($query, $role)
    {
        return $query->whereJsonContains('target_roles', $role);
    }

    public function scopePublished($query)
    {
        return $query->where('active', true)
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
