<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 * Verifies user has the required role(s) to access a route
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
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

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Access denied. You do not have permission to access this resource.',
            'required_roles' => $roles,
            'your_role' => $request->user()->role,
        ], 403);
    }
}
