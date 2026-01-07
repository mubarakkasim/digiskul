<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'priority',
        'target_type',
        'target_ids',
        'is_published',
        'is_dismissible',
        'show_on_login',
        'publish_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'target_ids' => 'array',
        'is_published' => 'boolean',
        'is_dismissible' => 'boolean',
        'show_on_login' => 'boolean',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the creator of this announcement
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if announcement is currently active
     */
    public function isActive(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        $now = now();

        if ($this->publish_at && $this->publish_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if announcement targets a specific school
     */
    public function targetsSchool(int $schoolId): bool
    {
        if ($this->target_type === 'all') {
            return true;
        }

        if ($this->target_type === 'specific_schools') {
            return in_array($schoolId, $this->target_ids ?? []);
        }

        return false;
    }

    /**
     * Check if announcement targets a specific role
     */
    public function targetsRole(string $role): bool
    {
        if ($this->target_type === 'all') {
            return true;
        }

        if ($this->target_type === 'specific_roles') {
            return in_array($role, $this->target_ids ?? []);
        }

        return true;
    }

    /**
     * Scope for published announcements
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for active announcements
     */
    public function scopeActive($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('publish_at')
                  ->orWhere('publish_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
            });
    }

    /**
     * Scope for login announcements
     */
    public function scopeForLogin($query)
    {
        return $query->active()->where('show_on_login', true);
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'red',
            'important' => 'orange',
            default => 'blue',
        };
    }
}
