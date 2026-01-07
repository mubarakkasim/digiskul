<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpersonationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'super_admin_id',
        'impersonated_user_id',
        'school_id',
        'reason',
        'ip_address',
        'user_agent',
        'started_at',
        'ended_at',
        'actions_performed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'actions_performed' => 'array',
    ];

    /**
     * Get the super admin who performed impersonation
     */
    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    /**
     * Get the user who was impersonated
     */
    public function impersonatedUser()
    {
        return $this->belongsTo(User::class, 'impersonated_user_id');
    }

    /**
     * Get the school context
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if session is still active
     */
    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    /**
     * Get session duration
     */
    public function getDurationAttribute(): ?string
    {
        $end = $this->ended_at ?? now();
        $diff = $this->started_at->diff($end);

        if ($diff->h > 0) {
            return $diff->format('%h hours %i minutes');
        }

        return $diff->format('%i minutes %s seconds');
    }

    /**
     * End the impersonation session
     */
    public function endSession(): self
    {
        $this->update(['ended_at' => now()]);
        return $this;
    }

    /**
     * Record an action performed during impersonation
     */
    public function recordAction(string $action, array $details = []): self
    {
        $actions = $this->actions_performed ?? [];
        $actions[] = [
            'action' => $action,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];

        $this->update(['actions_performed' => $actions]);
        return $this;
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope for sessions by a specific admin
     */
    public function scopeByAdmin($query, int $adminId)
    {
        return $query->where('super_admin_id', $adminId);
    }
}
