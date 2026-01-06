<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Get users for current school (School Admin)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = User::with(['school', 'roles']);
        
        // Filter by school for non-super-admin
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        } else if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('staff_id', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get all users across all schools (Super Admin only)
     */
    public function globalIndex(Request $request)
    {
        $query = User::with(['school', 'roles']);

        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:school_admin,teacher,class_teacher,student,parent,bursar,librarian,ict_officer',
            'staff_id' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'employment_date' => 'nullable|date',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        // Set school_id for non-super-admin
        if (!$currentUser->isSuperAdmin()) {
            $validated['school_id'] = $currentUser->school_id;
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);
        $validated['active'] = true;

        $user = User::create($validated);

        // Assign Spatie role
        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->assignRole($role);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('roles'),
        ], 201);
    }

    /**
     * Get user details
     */
    public function show(Request $request, $id)
    {
        $currentUser = $request->user();

        $query = User::with(['school', 'roles', 'teacherAssignments.classModel', 'teacherAssignments.subject']);
        
        if (!$currentUser->isSuperAdmin()) {
            $query->where('school_id', $currentUser->school_id);
        }

        $user = $query->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $currentUser = $request->user();

        $query = User::query();
        if (!$currentUser->isSuperAdmin()) {
            $query->where('school_id', $currentUser->school_id);
        }

        $user = $query->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role' => 'sometimes|string|in:school_admin,teacher,class_teacher,student,parent,bursar,librarian,ict_officer',
            'staff_id' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'qualification' => 'nullable|string',
            'employment_date' => 'nullable|date',
            'active' => 'sometimes|boolean',
        ]);

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Update Spatie role if changed
        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->load('roles'),
        ]);
    }

    /**
     * Delete user
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user();

        $query = User::query();
        if (!$currentUser->isSuperAdmin()) {
            $query->where('school_id', $currentUser->school_id);
        }

        $user = $query->findOrFail($id);

        // Prevent deleting self
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account',
            ], 403);
        }

        // Prevent deleting super admin
        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Super admin accounts cannot be deleted',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, $id)
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $query = User::query();
        if (!$currentUser->isSuperAdmin()) {
            $query->where('school_id', $currentUser->school_id);
        }

        $user = $query->findOrFail($id);

        // Can't assign super_admin role
        if ($validated['role'] === 'super_admin' && !$currentUser->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot assign super admin role',
            ], 403);
        }

        $user->update(['role' => $validated['role']]);
        $user->syncRoles([$validated['role']]);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully',
            'data' => $user->load('roles'),
        ]);
    }
}
