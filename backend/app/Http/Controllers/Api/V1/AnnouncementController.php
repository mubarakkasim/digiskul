<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List announcements (filtered by role)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Announcement::with('author:id,name');

        // Super admin sees all
        if ($user->isSuperAdmin()) {
            // Can filter by school
            if ($request->has('school_id')) {
                $query->where('school_id', $request->school_id);
            }
        } 
        // School admin sees their school's announcements
        elseif ($user->isSchoolAdmin()) {
            $query->where('school_id', $user->school_id)
                  ->orWhere('is_global', true);
        }
        // Other users see published announcements for their role
        else {
            $query->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhere('is_global', true);
            })
            ->whereJsonContains('target_roles', $user->role)
            ->published();
        }

        $announcements = $query->orderByDesc('published_at')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    /**
     * Get announcement details
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        
        $query = Announcement::with('author:id,name');

        if (!$user->isSuperAdmin()) {
            $query->where(function($q) use ($user) {
                $q->where('school_id', $user->school_id)
                  ->orWhere('is_global', true);
            });
            
            // Non-admins can only see published announcements for their role
            if (!$user->isSchoolAdmin()) {
                $query->whereJsonContains('target_roles', $user->role)->published();
            }
        }

        $announcement = $query->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $announcement,
        ]);
    }

    /**
     * Create announcement
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_roles' => 'required|array|min:1',
            'target_roles.*' => 'string|in:super_admin,school_admin,teacher,class_teacher,student,parent,bursar,librarian,ict_officer',
            'is_global' => 'sometimes|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        // Only super admin can create global announcements
        if (isset($validated['is_global']) && $validated['is_global'] && !$user->isSuperAdmin()) {
            $validated['is_global'] = false;
        }

        $validated['school_id'] = $user->isSuperAdmin() && isset($validated['is_global']) && $validated['is_global'] 
            ? null 
            : $user->school_id;
        $validated['created_by'] = $user->id;
        $validated['active'] = true;

        $announcement = Announcement::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully',
            'data' => $announcement,
        ], 201);
    }

    /**
     * Update announcement
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $query = Announcement::query();
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        $announcement = $query->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'target_roles' => 'sometimes|array|min:1',
            'target_roles.*' => 'string|in:super_admin,school_admin,teacher,class_teacher,student,parent,bursar,librarian,ict_officer',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'active' => 'sometimes|boolean',
        ]);

        $announcement->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => $announcement,
        ]);
    }

    /**
     * Delete announcement
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $query = Announcement::query();
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        $announcement = $query->findOrFail($id);
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully',
        ]);
    }

    /**
     * Publish announcement immediately
     */
    public function publish(Request $request, $id)
    {
        $user = $request->user();

        $query = Announcement::query();
        if (!$user->isSuperAdmin()) {
            $query->where('school_id', $user->school_id);
        }

        $announcement = $query->findOrFail($id);
        
        $announcement->update([
            'published_at' => now(),
            'active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement published successfully',
            'data' => $announcement,
        ]);
    }
}
