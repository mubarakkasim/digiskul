<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

/**
 * LogActivity Middleware
 * Logs user activities for audit trail (NDPR-compliant)
 */
class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if ($request->user()) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Log the activity
     */
    protected function logActivity(Request $request, Response $response): void
    {
        try {
            $user = $request->user();
            $method = $request->method();
            $path = $request->path();

            // Determine action type based on HTTP method
            $action = match($method) {
                'GET' => 'view',
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => 'access',
            };

            // Skip logging for certain routes
            $skipRoutes = [
                'api/v1/auth/me',
                'api/v1/dashboard/stats',
                'api/v1/locale',
                'sanctum/csrf-cookie',
            ];

            foreach ($skipRoutes as $skipRoute) {
                if (str_contains($path, $skipRoute)) {
                    return;
                }
            }

            // Extract entity type from path
            $entityType = $this->extractEntityType($path);

            // Don't log sensitive data in request body
            $safeInput = collect($request->except([
                'password',
                'password_confirmation',
                'current_password',
                'token',
                '_token',
            ]))->toArray();

            ActivityLog::create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $request->route('id') ?? null,
                'old_values' => null,
                'new_values' => $method !== 'GET' ? $safeInput : null,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'description' => "{$method} {$path}",
            ]);
        } catch (\Exception $e) {
            // Don't let logging errors break the application
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract entity type from request path
     */
    protected function extractEntityType(string $path): ?string
    {
        $segments = explode('/', $path);
        
        // Look for known entity types in path
        $entityTypes = [
            'students', 'teachers', 'users', 'classes', 'subjects',
            'attendance', 'grades', 'fees', 'payments', 'timetable',
            'duties', 'report-cards', 'announcements', 'archive',
            'schools', 'settings', 'reports',
        ];

        foreach ($segments as $segment) {
            if (in_array($segment, $entityTypes)) {
                return rtrim($segment, 's'); // Singularize
            }
        }

        return null;
    }
}
