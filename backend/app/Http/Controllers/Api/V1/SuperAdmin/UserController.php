<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * List all users across all schools (Global User Directory)
     */
    public function index(Request $request)
    {
        $query = User::with('school:id,name,subdomain');

        // Filter by school
        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get user details
     */
    public function show($id)
    {
        $user = User::with([
            'school:id,name,subdomain,email',
        ])->findOrFail($id);

        // Mask sensitive data
        $userData = $user->toArray();
        $userData['password'] = '********'; // Never expose password

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|string|in:super_admin,school_admin,teacher,class_teacher,student,parent,bursar,librarian,ict_officer',
            'active' => 'sometimes|boolean',
            'staff_id' => 'nullable|string|max:50',
        ]);

        $user->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['changes' => $validated])
            ->log('user_updated_by_super_admin');

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->fresh('school'),
        ]);
    }

    /**
     * Suspend user
     */
    public function suspend(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent suspending own account
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot suspend your own account',
            ], 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'active' => false,
            'meta' => array_merge($user->meta ?? [], [
                'suspended_at' => now()->toISOString(),
                'suspended_by' => auth()->id(),
                'suspend_reason' => $validated['reason'] ?? null,
            ]),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['reason' => $validated['reason'] ?? 'No reason provided'])
            ->log('user_suspended');

        return response()->json([
            'success' => true,
            'message' => 'User suspended successfully',
        ]);
    }

    /**
     * Activate user
     */
    public function activate($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'active' => true,
            'meta' => array_merge($user->meta ?? [], [
                'activated_at' => now()->toISOString(),
                'activated_by' => auth()->id(),
                'suspended_at' => null,
                'suspend_reason' => null,
            ]),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('user_activated');

        return response()->json([
            'success' => true,
            'message' => 'User activated successfully',
        ]);
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'new_password' => 'required|string|min:8',
            'send_email' => 'nullable|boolean',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'meta' => array_merge($user->meta ?? [], [
                'password_reset_at' => now()->toISOString(),
                'password_reset_by' => auth()->id(),
            ]),
        ]);

        // Optionally send email notification
        if ($request->boolean('send_email')) {
            // TODO: Send password reset notification email
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('user_password_reset');

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
        ]);
    }

    /**
     * Transfer user to different school
     */
    public function transfer(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'keep_role' => 'nullable|boolean',
        ]);

        $oldSchoolId = $user->school_id;
        
        $user->update([
            'school_id' => $validated['school_id'],
            'meta' => array_merge($user->meta ?? [], [
                'transferred_from' => $oldSchoolId,
                'transferred_at' => now()->toISOString(),
                'transferred_by' => auth()->id(),
            ]),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'from_school' => $oldSchoolId,
                'to_school' => $validated['school_id'],
            ])
            ->log('user_transferred');

        return response()->json([
            'success' => true,
            'message' => 'User transferred successfully',
            'data' => $user->fresh('school'),
        ]);
    }

    /**
     * Impersonate user (login as user for troubleshooting)
     */
    public function impersonate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Cannot impersonate self or other super admins
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot impersonate yourself',
            ], 400);
        }

        if ($user->role === 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot impersonate another Super Admin',
            ], 403);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Create impersonation log
        $log = ImpersonationLog::create([
            'super_admin_id' => auth()->id(),
            'impersonated_user_id' => $user->id,
            'school_id' => $user->school_id,
            'reason' => $validated['reason'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'started_at' => now(),
        ]);

        // Generate impersonation token
        $token = $user->createToken('impersonation', ['impersonation'])->plainTextToken;

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'reason' => $validated['reason'],
                'impersonation_log_id' => $log->id,
            ])
            ->log('user_impersonation_started');

        return response()->json([
            'success' => true,
            'message' => 'Impersonation session started',
            'data' => [
                'token' => $token,
                'user' => $user->only(['id', 'full_name', 'email', 'role', 'school_id']),
                'impersonation_log_id' => $log->id,
                'original_user_id' => auth()->id(),
            ],
        ]);
    }

    /**
     * End impersonation session
     */
    public function endImpersonation(Request $request, $logId)
    {
        $log = ImpersonationLog::where('super_admin_id', auth()->id())
            ->where('id', $logId)
            ->firstOrFail();

        $log->endSession();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['impersonation_log_id' => $logId])
            ->log('user_impersonation_ended');

        return response()->json([
            'success' => true,
            'message' => 'Impersonation session ended',
        ]);
    }

    /**
     * Get impersonation history
     */
    public function impersonationHistory(Request $request)
    {
        $query = ImpersonationLog::with([
            'superAdmin:id,full_name,email',
            'impersonatedUser:id,full_name,email,role',
            'school:id,name',
        ]);

        if ($request->has('admin_id')) {
            $query->where('super_admin_id', $request->admin_id);
        }

        $logs = $query->orderBy('started_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get user statistics
     */
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('active', true)->count(),
            'suspended_users' => User::where('active', false)->count(),
            'by_role' => User::selectRaw('role, count(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role'),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDay())
                ->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_this_month' => User::where('created_at', '>=', now()->subMonth())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Force logout user (terminate all sessions)
     */
    public function forceLogout($id)
    {
        $user = User::findOrFail($id);

        // Revoke all tokens
        $user->tokens()->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('user_force_logout');

        return response()->json([
            'success' => true,
            'message' => 'User logged out from all sessions',
        ]);
    }
}
