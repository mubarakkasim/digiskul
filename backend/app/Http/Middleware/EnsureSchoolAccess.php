<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;

/**
 * EnsureSchoolAccess Middleware
 * Ensures users can only access data within their assigned school (tenant isolation)
 */
class EnsureSchoolAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $user = $request->user();

        // Super admin can access any school
        if ($user->hasRole('super_admin')) {
            // If school_id is in request, validate it exists
            if ($request->has('school_id')) {
                $school = School::find($request->school_id);
                if (!$school) {
                    return response()->json([
                        'success' => false,
                        'message' => 'School not found.',
                    ], 404);
                }
            }
            return $next($request);
        }

        // For other users, they must have a school_id
        if (!$user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You are not assigned to any school.',
            ], 403);
        }

        // Verify their school is active
        $school = School::find($user->school_id);
        if (!$school || !$school->active) {
            return response()->json([
                'success' => false,
                'message' => 'Your school account is inactive or suspended. Please contact administrator.',
            ], 403);
        }

        // Check license validity
        if ($school->license_valid_until && $school->license_valid_until->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Your school license has expired. Please contact administrator.',
            ], 403);
        }

        // Set school context for the request
        $request->merge(['school_id' => $user->school_id]);

        return $next($request);
    }
}
