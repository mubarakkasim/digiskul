<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SchoolSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'license_plan_id',
        'start_date',
        'end_date',
        'status',
        'auto_renew',
        'payment_reference',
        'payment_method',
        'amount_paid',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'amount_paid' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Get the school for this subscription
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the license plan for this subscription
     */
    public function licensePlan()
    {
        return $this->belongsTo(LicensePlan::class);
    }

    /**
     * Get the user who created this subscription
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    /**
     * Check if subscription is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        return $this->end_date->isFuture() && 
               $this->end_date->diffInDays(now()) <= 30;
    }

    /**
     * Get days until expiration
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->end_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->end_date);
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope for expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
                     ->where('end_date', '>=', now())
                     ->where('end_date', '<=', now()->addDays($days));
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now())
                     ->where('status', '!=', 'cancelled');
    }

    /**
     * Renew subscription
     */
    public function renew(int $months = null): self
    {
        $months = $months ?? $this->licensePlan->duration_months;
        
        $this->update([
            'start_date' => now(),
            'end_date' => now()->addMonths($months),
            'status' => 'active',
        ]);

        return $this;
    }
}
