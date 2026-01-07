<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     * Ensure the user is a Super Admin and log all actions.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Authentication required.',
                'error' => 'unauthenticated',
            ], 401);
        }

        // Check if user is Super Admin
        if ($request->user()->role !== 'super_admin') {
            // Log unauthorized access attempt
            activity()
                ->causedBy($request->user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'route' => $request->path(),
                    'method' => $request->method(),
                ])
                ->log('permission_denied');

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin access required.',
                'error' => 'insufficient_permissions',
            ], 403);
        }

        // Log all super admin actions for audit trail
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->path(),
                'method' => $request->method(),
                'params' => $request->except(['password', 'new_password', 'api_key', 'secret_key']),
            ])
            ->log('super_admin_access');

        return $next($request);
    }
}
