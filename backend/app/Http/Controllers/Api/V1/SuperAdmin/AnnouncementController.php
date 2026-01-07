<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemAnnouncement;
use App\Models\School;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * List all system announcements
     */
    public function index(Request $request)
    {
        $query = SystemAnnouncement::with('creator:id,full_name,email');

        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('active')) {
            if ($request->boolean('active')) {
                $query->active();
            }
        }

        $announcements = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    /**
     * Get announcement details
     */
    public function show($id)
    {
        $announcement = SystemAnnouncement::with('creator:id,full_name,email')
            ->findOrFail($id);

        // Get target details if specific
        $targetDetails = null;
        if ($announcement->target_type === 'specific_schools' && $announcement->target_ids) {
            $targetDetails = School::whereIn('id', $announcement->target_ids)
                ->get(['id', 'name', 'subdomain']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'announcement' => $announcement,
                'target_details' => $targetDetails,
            ],
        ]);
    }

    /**
     * Create a new system announcement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'nullable|in:normal,important,critical',
            'target_type' => 'required|in:all,specific_schools,specific_roles,specific_plans',
            'target_ids' => 'nullable|array',
            'is_dismissible' => 'nullable|boolean',
            'show_on_login' => 'nullable|boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:publish_at',
            'is_published' => 'nullable|boolean',
        ]);

        // Validate target_ids based on target_type
        if ($validated['target_type'] === 'specific_schools' && !empty($validated['target_ids'])) {
            $validSchools = School::whereIn('id', $validated['target_ids'])->count();
            if ($validSchools !== count($validated['target_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some target school IDs are invalid',
                ], 422);
            }
        }

        $announcement = SystemAnnouncement::create([
            ...$validated,
            'created_by' => auth()->id(),
            'priority' => $validated['priority'] ?? 'normal',
            'is_published' => $validated['is_published'] ?? false,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($announcement)
            ->withProperties(['title' => $announcement->title])
            ->log('system_announcement_created');

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
        $announcement = SystemAnnouncement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'priority' => 'nullable|in:normal,important,critical',
            'target_type' => 'sometimes|in:all,specific_schools,specific_roles,specific_plans',
            'target_ids' => 'nullable|array',
            'is_dismissible' => 'nullable|boolean',
            'show_on_login' => 'nullable|boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_published' => 'nullable|boolean',
        ]);

        $announcement->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($announcement)
            ->withProperties(['changes' => $validated])
            ->log('system_announcement_updated');

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => $announcement,
        ]);
    }

    /**
     * Publish announcement
     */
    public function publish($id)
    {
        $announcement = SystemAnnouncement::findOrFail($id);

        $announcement->update([
            'is_published' => true,
            'publish_at' => $announcement->publish_at ?? now(),
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($announcement)
            ->log('system_announcement_published');

        return response()->json([
            'success' => true,
            'message' => 'Announcement published successfully',
            'data' => $announcement,
        ]);
    }

    /**
     * Unpublish announcement
     */
    public function unpublish($id)
    {
        $announcement = SystemAnnouncement::findOrFail($id);

        $announcement->update(['is_published' => false]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($announcement)
            ->log('system_announcement_unpublished');

        return response()->json([
            'success' => true,
            'message' => 'Announcement unpublished',
        ]);
    }

    /**
     * Delete announcement
     */
    public function destroy($id)
    {
        $announcement = SystemAnnouncement::findOrFail($id);

        $announcement->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['title' => $announcement->title])
            ->log('system_announcement_deleted');

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully',
        ]);
    }

    /**
     * Get active announcements for current user/context
     * (Used by frontend to display banners)
     */
    public function active(Request $request)
    {
        $schoolId = $request->get('school_id');
        $role = $request->get('role');
        $plan = $request->get('plan');

        $announcements = SystemAnnouncement::active()
            ->orderBy('priority', 'desc')
            ->orderBy('publish_at', 'desc')
            ->get()
            ->filter(function ($announcement) use ($schoolId, $role, $plan) {
                switch ($announcement->target_type) {
                    case 'all':
                        return true;
                    case 'specific_schools':
                        return $schoolId && in_array($schoolId, $announcement->target_ids ?? []);
                    case 'specific_roles':
                        return $role && in_array($role, $announcement->target_ids ?? []);
                    case 'specific_plans':
                        return $plan && in_array($plan, $announcement->target_ids ?? []);
                    default:
                        return true;
                }
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    /**
     * Get login screen announcements
     */
    public function loginAnnouncements()
    {
        $announcements = SystemAnnouncement::forLogin()
            ->orderBy('priority', 'desc')
            ->limit(3)
            ->get(['id', 'title', 'content', 'priority']);

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }
}
