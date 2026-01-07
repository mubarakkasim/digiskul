<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * Get activity logs (cross-school)
     */
    public function activity(Request $request)
    {
        $query = ActivityLog::with([
            'user:id,full_name,email,role',
            'school:id,name,subdomain',
        ]);

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by entity type
        if ($request->has('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get security logs (authentication, access violations)
     */
    public function security(Request $request)
    {
        $securityActions = [
            'login', 'logout', 'login_failed', 'password_reset',
            'password_changed', 'mfa_enabled', 'mfa_disabled',
            'user_suspended', 'user_activated', 'permission_denied',
            'backup_downloaded', 'user_impersonation_started', 
            'user_impersonation_ended', 'force_logout',
        ];

        $query = ActivityLog::with([
            'user:id,full_name,email,role',
            'school:id,name,subdomain',
        ])->whereIn('action', $securityActions);

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by severity (based on action type)
        if ($request->has('severity')) {
            switch ($request->severity) {
                case 'high':
                    $query->whereIn('action', [
                        'login_failed', 'permission_denied', 
                        'user_suspended', 'backup_downloaded',
                        'user_impersonation_started',
                    ]);
                    break;
                case 'medium':
                    $query->whereIn('action', [
                        'password_reset', 'password_changed',
                        'mfa_disabled', 'force_logout',
                    ]);
                    break;
            }
        }

        // Filter by date
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get system logs (errors, system events)
     */
    public function system(Request $request)
    {
        $systemActions = [
            'error', 'exception', 'backup_created', 'backup_failed',
            'backup_restored', 'maintenance_enabled', 'maintenance_disabled',
            'cache_cleared', 'migration_run', 'system_updated',
            'platform_settings_updated', 'feature_toggled',
        ];

        $query = ActivityLog::with(['user:id,full_name,email'])
            ->whereIn('action', $systemActions)
            ->orWhereNull('school_id'); // System-wide logs often have no school

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get log details
     */
    public function show($id)
    {
        $log = ActivityLog::with([
            'user:id,full_name,email,role,phone',
            'school:id,name,subdomain',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Get log statistics
     */
    public function stats(Request $request)
    {
        $days = $request->get('days', 30);
        $fromDate = now()->subDays($days);

        $stats = [
            'total_logs' => ActivityLog::where('created_at', '>=', $fromDate)->count(),
            'by_action' => ActivityLog::where('created_at', '>=', $fromDate)
                ->selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->limit(20)
                ->pluck('count', 'action'),
            'by_school' => ActivityLog::where('created_at', '>=', $fromDate)
                ->whereNotNull('school_id')
                ->selectRaw('school_id, count(*) as count')
                ->groupBy('school_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'failed_logins' => ActivityLog::where('created_at', '>=', $fromDate)
                ->where('action', 'login_failed')
                ->count(),
            'daily_activity' => ActivityLog::where('created_at', '>=', $fromDate)
                ->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Export logs
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,json',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'school_id' => 'nullable|exists:schools,id',
            'action' => 'nullable|string',
        ]);

        $query = ActivityLog::with(['user:id,full_name,email', 'school:id,name'])
            ->whereDate('created_at', '>=', $validated['from_date'])
            ->whereDate('created_at', '<=', $validated['to_date']);

        if (!empty($validated['school_id'])) {
            $query->where('school_id', $validated['school_id']);
        }

        if (!empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->limit(10000) // Limit export size
            ->get();

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'format' => $validated['format'],
                'count' => $logs->count(),
                'date_range' => [$validated['from_date'], $validated['to_date']],
            ])
            ->log('logs_exported');

        if ($validated['format'] === 'json') {
            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        }

        // CSV export
        $csv = "ID,Date,User,School,Action,Entity Type,Entity ID,Description,IP Address\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                $log->id,
                $log->created_at->toISOString(),
                '"' . ($log->user->full_name ?? 'N/A') . '"',
                '"' . ($log->school->name ?? 'System') . '"',
                $log->action,
                $log->entity_type ?? 'N/A',
                $log->entity_id ?? 'N/A',
                '"' . str_replace('"', '""', $log->description ?? '') . '"',
                $log->ip_address ?? 'N/A',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Get available action types for filtering
     */
    public function actionTypes()
    {
        $actions = ActivityLog::distinct()
            ->pluck('action')
            ->sort()
            ->values();

        return response()->json([
            'success' => true,
            'data' => $actions,
        ]);
    }
}
