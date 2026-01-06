<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs for current school
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = ActivityLog::with(['user:id,name,email,role']);

        // Filter by school
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        } elseif ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action type
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

        $logs = $query->orderByDesc('created_at')->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get system-wide logs (Super Admin only)
     */
    public function systemLogs(Request $request)
    {
        $query = ActivityLog::with(['user:id,name,email,role', 'school:id,name']);

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
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

        // Default to last 30 days if no date filter
        if (!$request->has('from_date') && !$request->has('to_date')) {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        $logs = $query->orderByDesc('created_at')->paginate($request->get('per_page', 100));

        // Get summary stats
        $stats = [
            'total_actions' => ActivityLog::count(),
            'actions_today' => ActivityLog::whereDate('created_at', today())->count(),
            'actions_by_type' => ActivityLog::selectRaw('action, count(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action'),
            'most_active_users' => ActivityLog::selectRaw('user_id, count(*) as count')
                ->with('user:id,name')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->take(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $logs,
            'stats' => $stats,
        ]);
    }
}
