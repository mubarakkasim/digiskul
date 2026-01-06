<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $user = $request->user();
        $userRole = $user->role;

        // Super admin always has access (check both role column and Spatie)
        if ($userRole === 'super_admin' || $this->userHasRole($user, 'super_admin')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            // Check role column first (faster)
            if ($userRole === $role) {
                return $next($request);
            }
            
            // Also check Spatie role
            if ($this->userHasRole($user, $role)) {
                return $next($request);
            }
        }

        Log::warning('Access denied', [
            'user_id' => $user->id,
            'required_roles' => $roles,
            'user_role' => $userRole,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Access denied. You do not have permission to access this resource.',
            'required_roles' => $roles,
            'your_role' => $userRole,
        ], 403);
    }

    /**
     * Check if user has a role using Spatie (with error handling)
     */
    protected function userHasRole($user, $role): bool
    {
        try {
            return method_exists($user, 'hasRole') && $user->hasRole($role);
        } catch (\Exception $e) {
            Log::error('Error checking user role: ' . $e->getMessage());
            return false;
        }
    }
}

