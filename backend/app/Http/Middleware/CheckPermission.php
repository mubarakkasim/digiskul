<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission Middleware
 * Verifies user has the required permission(s) to access a route
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        // Super admin always has access
        if ($request->user()->hasRole('super_admin')) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        foreach ($permissions as $permission) {
            if ($request->user()->can($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Access denied. You do not have the required permissions.',
            'required_permissions' => $permissions,
        ], 403);
    }
}
