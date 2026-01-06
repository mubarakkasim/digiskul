<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    /**
     * List all schools (Super Admin Only)
     */
    public function index(Request $request)
    {
        $query = School::withCount(['users', 'students', 'classes']);

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $schools = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $schools,
        ]);
    }

    /**
     * Create a new school
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|unique:schools,subdomain|alpha_dash',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'subscription_plan' => 'nullable|string|in:basic,standard,premium,enterprise',
            'license_valid_until' => 'nullable|date',
        ]);

        $validated['active'] = true;
        $validated['subscription_plan'] = $validated['subscription_plan'] ?? 'basic';
        $validated['license_valid_until'] = $validated['license_valid_until'] ?? now()->addYear();

        $school = School::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'School created successfully',
            'data' => $school,
        ], 201);
    }

    /**
     * Get school details
     */
    public function show($id)
    {
        $school = School::withCount(['users', 'students', 'classes'])
            ->with(['users' => function($q) {
                $q->where('role', 'school_admin');
            }])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $school,
        ]);
    }

    /**
     * Update school
     */
    public function update(Request $request, $id)
    {
        $school = School::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'subdomain' => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('schools')->ignore($id)],
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo' => 'nullable|string',
            'subscription_plan' => 'nullable|string|in:basic,standard,premium,enterprise',
            'license_valid_until' => 'nullable|date',
            'active' => 'sometimes|boolean',
            'meta' => 'nullable|array',
        ]);

        $school->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'School updated successfully',
            'data' => $school,
        ]);
    }

    /**
     * Delete school
     */
    public function destroy($id)
    {
        $school = School::findOrFail($id);

        // Don't hard delete, just deactivate
        $school->update(['active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'School deactivated successfully',
        ]);
    }

    /**
     * Suspend school
     */
    public function suspend(Request $request, $id)
    {
        $school = School::findOrFail($id);
        
        $school->update([
            'active' => false,
            'meta' => array_merge($school->meta ?? [], [
                'suspended_at' => now(),
                'suspended_reason' => $request->reason ?? 'No reason provided',
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School suspended successfully',
        ]);
    }

    /**
     * Activate school
     */
    public function activate($id)
    {
        $school = School::findOrFail($id);
        
        $school->update([
            'active' => true,
            'meta' => array_merge($school->meta ?? [], [
                'suspended_at' => null,
                'suspended_reason' => null,
                'activated_at' => now(),
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'School activated successfully',
        ]);
    }

    /**
     * Update school features (enable/disable modules)
     */
    public function updateFeatures(Request $request, $id)
    {
        $school = School::findOrFail($id);

        $validated = $request->validate([
            'features' => 'required|array',
            'features.*.feature' => 'required|string',
            'features.*.enabled' => 'required|boolean',
            'features.*.config' => 'nullable|array',
        ]);

        // Store features in meta
        $meta = $school->meta ?? [];
        $meta['features'] = collect($validated['features'])->keyBy('feature')->toArray();
        
        $school->update(['meta' => $meta]);

        return response()->json([
            'success' => true,
            'message' => 'School features updated successfully',
            'data' => $school,
        ]);
    }

    /**
     * Get school analytics
     */
    public function analytics($id)
    {
        $school = School::withCount(['users', 'students', 'classes'])->findOrFail($id);

        // Get user counts by role
        $usersByRole = User::where('school_id', $id)
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        // Get activity stats (last 30 days)
        $activityCount = \App\Models\ActivityLog::where('school_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'school' => $school,
                'users_by_role' => $usersByRole,
                'recent_activities' => $activityCount,
                'subscription' => [
                    'plan' => $school->subscription_plan,
                    'valid_until' => $school->license_valid_until,
                    'is_expired' => $school->license_valid_until && $school->license_valid_until->isPast(),
                ],
            ],
        ]);
    }

    /**
     * Get system-wide settings
     */
    public function systemSettings()
    {
        $settings = \DB::table('system_settings')->get()->keyBy('key');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSystemSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'settings.*.type' => 'nullable|string|in:string,json,boolean,integer',
        ]);

        foreach ($validated['settings'] as $setting) {
            \DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => is_array($setting['value']) ? json_encode($setting['value']) : $setting['value'],
                    'type' => $setting['type'] ?? 'string',
                    'updated_at' => now(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'System settings updated successfully',
        ]);
    }

    /**
     * Get system-wide analytics
     */
    public function systemAnalytics()
    {
        $stats = [
            'total_schools' => School::count(),
            'active_schools' => School::where('active', true)->count(),
            'total_users' => User::count(),
            'total_students' => \App\Models\Student::count(),
            'schools_by_plan' => School::selectRaw('subscription_plan, count(*) as count')
                ->groupBy('subscription_plan')
                ->pluck('count', 'subscription_plan'),
            'users_by_role' => User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
            'recent_activities' => \App\Models\ActivityLog::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
