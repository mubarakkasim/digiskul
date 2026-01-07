<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureRegistry extends Model
{
    use HasFactory;

    protected $table = 'feature_registry';

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'icon',
        'is_active',
        'is_premium',
        'dependencies',
        'plans',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'dependencies' => 'array',
        'plans' => 'array',
    ];

    /**
     * Feature categories
     */
    const CATEGORY_ACADEMIC = 'academic';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_COMMUNICATION = 'communication';
    const CATEGORY_REPORTING = 'reporting';
    const CATEGORY_AI = 'ai';
    const CATEGORY_INTEGRATION = 'integration';

    /**
     * Check if feature is available for a plan
     */
    public function isAvailableForPlan(string $planCode): bool
    {
        if (empty($this->plans)) {
            return true; // Available on all plans by default
        }

        return in_array($planCode, $this->plans);
    }

    /**
     * Check if feature has unmet dependencies
     */
    public function hasUnmetDependencies(array $enabledFeatures): bool
    {
        if (empty($this->dependencies)) {
            return false;
        }

        foreach ($this->dependencies as $dependency) {
            if (!in_array($dependency, $enabledFeatures)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope for active features
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for premium features
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    /**
     * Scope by category
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get features grouped by category
     */
    public static function getGroupedByCategory(): array
    {
        return self::active()
            ->orderBy('category')
            ->orderBy('priority', 'desc')
            ->get()
            ->groupBy('category')
            ->toArray();
    }
}
