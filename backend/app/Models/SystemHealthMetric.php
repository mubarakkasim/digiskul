<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemHealthMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_type',
        'metric_name',
        'value',
        'unit',
        'meta',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'meta' => 'array',
        'recorded_at' => 'datetime',
    ];

    /**
     * Metric types
     */
    const TYPE_CPU = 'cpu';
    const TYPE_MEMORY = 'memory';
    const TYPE_DISK = 'disk';
    const TYPE_DATABASE = 'database';
    const TYPE_QUEUE = 'queue';
    const TYPE_API = 'api';
    const TYPE_CACHE = 'cache';

    /**
     * Record a new metric value
     */
    public static function record(string $type, string $name, float $value, string $unit = null, array $meta = []): self
    {
        return self::create([
            'metric_type' => $type,
            'metric_name' => $name,
            'value' => $value,
            'unit' => $unit,
            'meta' => $meta,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Get latest value for a metric
     */
    public static function getLatest(string $type, string $name): ?self
    {
        return self::where('metric_type', $type)
            ->where('metric_name', $name)
            ->orderBy('recorded_at', 'desc')
            ->first();
    }

    /**
     * Get average value for a period
     */
    public static function getAverage(string $type, string $name, int $hours = 24): ?float
    {
        return self::where('metric_type', $type)
            ->where('metric_name', $name)
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->avg('value');
    }

    /**
     * Scope by metric type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope for recent metrics
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', now()->subHours($hours));
    }

    /**
     * Clean up old metrics
     */
    public static function cleanup(int $days = 30): int
    {
        return self::where('recorded_at', '<', now()->subDays($days))->delete();
    }
}
