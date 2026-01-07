<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicensePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'duration_months',
        'user_limit',
        'student_limit',
        'storage_gb',
        'price',
        'currency',
        'features',
        'trial_days',
        'priority',
        'is_active',
        'is_public',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'storage_gb' => 'decimal:2',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Get subscriptions for this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    /**
     * Get active subscriptions count
     */
    public function getActiveSubscriptionsCountAttribute()
    {
        return $this->subscriptions()->where('status', 'active')->count();
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public plans
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Check if feature is included in this plan
     */
    public function hasFeature(string $featureCode): bool
    {
        $features = $this->features ?? [];
        return in_array($featureCode, $features);
    }

    /**
     * Format price for display
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }
}
