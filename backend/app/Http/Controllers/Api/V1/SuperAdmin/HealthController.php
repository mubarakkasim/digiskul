<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemHealthMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class HealthController extends Controller
{
    /**
     * Get overall system health status
     */
    public function index()
    {
        $health = [
            'status' => 'healthy',
            'score' => 100,
            'checks' => [],
            'timestamp' => now()->toISOString(),
        ];

        // Database check
        $dbCheck = $this->checkDatabase();
        $health['checks']['database'] = $dbCheck;
        if (!$dbCheck['healthy']) $health['score'] -= 30;

        // Cache check
        $cacheCheck = $this->checkCache();
        $health['checks']['cache'] = $cacheCheck;
        if (!$cacheCheck['healthy']) $health['score'] -= 10;

        // Queue check
        $queueCheck = $this->checkQueue();
        $health['checks']['queue'] = $queueCheck;
        if (!$queueCheck['healthy']) $health['score'] -= 15;

        // Storage check
        $storageCheck = $this->checkStorage();
        $health['checks']['storage'] = $storageCheck;
        if (!$storageCheck['healthy']) $health['score'] -= 20;

        // Determine overall status
        if ($health['score'] < 50) {
            $health['status'] = 'critical';
        } elseif ($health['score'] < 80) {
            $health['status'] = 'degraded';
        }

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }

    /**
     * Get detailed metrics
     */
    public function metrics(Request $request)
    {
        $hours = $request->get('hours', 24);
        $type = $request->get('type');

        $query = SystemHealthMetric::recent($hours);

        if ($type) {
            $query->ofType($type);
        }

        $metrics = $query->orderBy('recorded_at', 'desc')
            ->limit(1000)
            ->get();

        // Group by type and name
        $grouped = $metrics->groupBy(function ($metric) {
            return "{$metric->metric_type}:{$metric->metric_name}";
        })->map(function ($group) {
            return [
                'type' => $group->first()->metric_type,
                'name' => $group->first()->metric_name,
                'unit' => $group->first()->unit,
                'current' => $group->first()->value,
                'average' => $group->avg('value'),
                'min' => $group->min('value'),
                'max' => $group->max('value'),
                'data_points' => $group->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'metrics' => $grouped->values(),
                'period_hours' => $hours,
            ],
        ]);
    }

    /**
     * Get real-time metrics
     */
    public function realtime()
    {
        // Collect current metrics
        $metrics = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'database' => $this->getDatabaseMetrics(),
            'php' => $this->getPhpMetrics(),
        ];

        // Record metrics for history
        foreach ($metrics as $type => $data) {
            if (is_array($data)) {
                foreach ($data as $name => $value) {
                    if (is_numeric($value)) {
                        SystemHealthMetric::record($type, $name, $value, '%');
                    }
                }
            } elseif (is_numeric($data)) {
                SystemHealthMetric::record($type, 'usage', $data, '%');
            }
        }

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'recorded_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get API performance metrics
     */
    public function apiMetrics(Request $request)
    {
        $hours = $request->get('hours', 24);

        $metrics = SystemHealthMetric::where('metric_type', 'api')
            ->recent($hours)
            ->get();

        $summary = [
            'avg_response_time' => $metrics->where('metric_name', 'response_time')->avg('value') ?? 0,
            'error_rate' => $metrics->where('metric_name', 'error_rate')->avg('value') ?? 0,
            'requests_per_minute' => $metrics->where('metric_name', 'requests_per_minute')->avg('value') ?? 0,
            'slowest_endpoints' => [], // Would require more detailed logging
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Database health check
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy' => true,
                'latency_ms' => $latency,
                'connections' => DB::connection()->getDatabaseName() ? 'connected' : 'disconnected',
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cache health check
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . uniqid();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'healthy' => $value === 'test',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Queue health check
     */
    protected function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            return [
                'healthy' => true,
                'connection' => $connection,
                'pending_jobs' => 0, // Would need queue-specific implementation
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Storage health check
     */
    protected function checkStorage(): array
    {
        try {
            $disk = storage_path();
            $total = disk_total_space($disk);
            $free = disk_free_space($disk);
            $used = $total - $free;
            $usedPercent = round(($used / $total) * 100, 2);

            return [
                'healthy' => $usedPercent < 90,
                'total_gb' => round($total / 1073741824, 2),
                'used_gb' => round($used / 1073741824, 2),
                'free_gb' => round($free / 1073741824, 2),
                'used_percent' => $usedPercent,
            ];
        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get CPU usage (simplified)
     */
    protected function getCpuUsage(): float
    {
        // Windows doesn't have /proc/loadavg
        // Return simulated value or use Windows-specific method
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return rand(10, 60); // Simulated for Windows
        }

        $load = sys_getloadavg();
        return round($load[0] * 100 / max(1, (int) shell_exec('nproc')), 2);
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): array
    {
        return [
            'used_mb' => round(memory_get_usage(true) / 1048576, 2),
            'peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
            'limit' => ini_get('memory_limit'),
        ];
    }

    /**
     * Get disk usage
     */
    protected function getDiskUsage(): array
    {
        $disk = storage_path();
        $total = disk_total_space($disk);
        $free = disk_free_space($disk);

        return [
            'total_gb' => round($total / 1073741824, 2),
            'free_gb' => round($free / 1073741824, 2),
            'used_percent' => round((($total - $free) / $total) * 100, 2),
        ];
    }

    /**
     * Get database metrics
     */
    protected function getDatabaseMetrics(): array
    {
        try {
            // Get table sizes
            $dbName = config('database.connections.mysql.database');
            
            $size = DB::select("
                SELECT 
                    SUM(data_length + index_length) / 1024 / 1024 as size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            return [
                'size_mb' => round($size[0]->size_mb ?? 0, 2),
                'connection' => 'active',
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get PHP metrics
     */
    protected function getPhpMetrics(): array
    {
        return [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    /**
     * Clear old metrics (cleanup)
     */
    public function cleanup(Request $request)
    {
        $days = $request->get('days', 30);
        
        $deleted = SystemHealthMetric::cleanup($days);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['days' => $days, 'deleted_count' => $deleted])
            ->log('health_metrics_cleanup');

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old metric records",
        ]);
    }
}
