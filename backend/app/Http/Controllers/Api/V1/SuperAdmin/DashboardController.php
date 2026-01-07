<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\ActivityLog;
use App\Models\SchoolSubscription;
use App\Models\SystemHealthMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get Super Admin dashboard statistics
     */
    public function stats(Request $request)
    {
        // Core metrics
        $totalSchools = School::count();
        $activeSchools = School::where('active', true)->count();
        $suspendedSchools = School::where('active', false)->count();
        $trialSchools = School::where('status', 'trial')->count();
        
        // User metrics
        $totalUsers = User::count();
        $totalStudents = Student::count();
        
        // Active sessions (users logged in within last 24 hours)
        $activeSessions = User::where('last_login_at', '>=', now()->subDay())->count();
        
        // License metrics
        $expiringLicenses = SchoolSubscription::expiringSoon(30)->count();
        $expiredLicenses = SchoolSubscription::expired()->count();
        
        // Calculate system health score (0-100)
        $healthScore = $this->calculateHealthScore();
        
        // Recent activities
        $recentActivities = ActivityLog::with('user:id,full_name,email')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'description' => $log->description,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->full_name,
                    ] : null,
                    'school_id' => $log->school_id,
                    'created_at' => $log->created_at,
                ];
            });

        // Schools by plan
        $schoolsByPlan = School::selectRaw('subscription_plan, count(*) as count')
            ->groupBy('subscription_plan')
            ->pluck('count', 'subscription_plan');

        // Recent signups (last 7 days)
        $recentSignups = School::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'subdomain', 'created_at']);

        // Monthly growth (schools created per month, last 6 months)
        $monthlyGrowth = School::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        return response()->json([
            'success' => true,
            'data' => [
                'schools' => [
                    'total' => $totalSchools,
                    'active' => $activeSchools,
                    'suspended' => $suspendedSchools,
                    'trial' => $trialSchools,
                ],
                'users' => [
                    'total' => $totalUsers,
                    'active_sessions' => $activeSessions,
                ],
                'students' => [
                    'total' => $totalStudents,
                ],
                'licenses' => [
                    'expiring_soon' => $expiringLicenses,
                    'expired' => $expiredLicenses,
                ],
                'health_score' => $healthScore,
                'schools_by_plan' => $schoolsByPlan,
                'recent_activities' => $recentActivities,
                'recent_signups' => $recentSignups,
                'monthly_growth' => $monthlyGrowth,
            ],
        ]);
    }

    /**
     * Get recent platform activity feed
     */
    public function recentActivity(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        
        $activities = ActivityLog::with(['user:id,full_name,email', 'school:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Get real-time metrics
     */
    public function realTimeMetrics()
    {
        // Get latest metrics
        $metrics = [
            'cpu' => SystemHealthMetric::getLatest('cpu', 'usage'),
            'memory' => SystemHealthMetric::getLatest('memory', 'usage'),
            'disk' => SystemHealthMetric::getLatest('disk', 'usage'),
            'database' => [
                'connections' => SystemHealthMetric::getLatest('database', 'connections'),
                'query_time' => SystemHealthMetric::getLatest('database', 'avg_query_time'),
            ],
            'api' => [
                'response_time' => SystemHealthMetric::getLatest('api', 'avg_response_time'),
                'error_rate' => SystemHealthMetric::getLatest('api', 'error_rate'),
            ],
        ];

        // Convert to simple values
        $formatted = [];
        foreach ($metrics as $key => $metric) {
            if (is_array($metric)) {
                $formatted[$key] = [];
                foreach ($metric as $subKey => $subMetric) {
                    $formatted[$key][$subKey] = $subMetric ? $subMetric->value : 0;
                }
            } else {
                $formatted[$key] = $metric ? $metric->value : 0;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Calculate system health score (0-100)
     */
    protected function calculateHealthScore(): int
    {
        $score = 100;
        
        // Deduct for suspended schools (max -10)
        $suspendedRatio = School::where('active', false)->count() / max(School::count(), 1);
        $score -= min(10, $suspendedRatio * 20);
        
        // Deduct for expired licenses (max -15)
        $expiredCount = SchoolSubscription::expired()->count();
        $score -= min(15, $expiredCount * 3);
        
        // Deduct for error logs in last 24 hours (max -20)
        $errorCount = ActivityLog::where('action', 'error')
            ->where('created_at', '>=', now()->subDay())
            ->count();
        $score -= min(20, $errorCount);
        
        // Check system metrics if available
        $cpuMetric = SystemHealthMetric::getLatest('cpu', 'usage');
        if ($cpuMetric && $cpuMetric->value > 80) {
            $score -= 10;
        }
        
        $memoryMetric = SystemHealthMetric::getLatest('memory', 'usage');
        if ($memoryMetric && $memoryMetric->value > 85) {
            $score -= 10;
        }
        
        return max(0, min(100, (int) $score));
    }

    /**
     * Get critical alerts
     */
    public function alerts()
    {
        $alerts = [];

        // Check for expired subscriptions
        $expiredCount = SchoolSubscription::expired()->count();
        if ($expiredCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Expired Subscriptions',
                'message' => "{$expiredCount} school(s) have expired subscriptions",
                'action' => '/super-admin/licenses/subscriptions?status=expired',
            ];
        }

        // Check for expiring soon
        $expiringCount = SchoolSubscription::expiringSoon(7)->count();
        if ($expiringCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Expiring Soon',
                'message' => "{$expiringCount} subscription(s) expiring within 7 days",
                'action' => '/super-admin/licenses/subscriptions?expiring=7',
            ];
        }

        // Check for suspended schools
        $suspendedCount = School::where('active', false)->count();
        if ($suspendedCount > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Suspended Schools',
                'message' => "{$suspendedCount} school(s) are currently suspended",
                'action' => '/super-admin/schools?status=suspended',
            ];
        }

        // Check system health
        $healthScore = $this->calculateHealthScore();
        if ($healthScore < 70) {
            $alerts[] = [
                'type' => 'error',
                'title' => 'Low Health Score',
                'message' => "System health score is {$healthScore}%. Review system status.",
                'action' => '/super-admin/health',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }
}
