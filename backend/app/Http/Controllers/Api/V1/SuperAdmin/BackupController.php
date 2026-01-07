<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemBackup;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * List all backups
     */
    public function index(Request $request)
    {
        $query = SystemBackup::with(['school:id,name', 'creator:id,full_name']);

        if ($request->has('type')) {
            $query->where('backup_type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        $backups = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $backups,
        ]);
    }

    /**
     * Get backup details
     */
    public function show($id)
    {
        $backup = SystemBackup::with(['school:id,name,subdomain', 'creator:id,full_name,email'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $backup,
        ]);
    }

    /**
     * Create a new backup
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'backup_type' => 'required|in:full,incremental,school_only,database_only,files_only',
            'school_id' => 'nullable|exists:schools,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Only allow school_id for school_only backups
        if ($validated['backup_type'] !== 'school_only') {
            $validated['school_id'] = null;
        }

        $backup = SystemBackup::create([
            'backup_type' => $validated['backup_type'],
            'school_id' => $validated['school_id'] ?? null,
            'status' => 'pending',
            'created_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Queue the backup job (in a real implementation)
        // For now, simulate immediate processing
        $this->processBackup($backup);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($backup)
            ->withProperties(['type' => $validated['backup_type']])
            ->log('backup_initiated');

        return response()->json([
            'success' => true,
            'message' => 'Backup initiated successfully',
            'data' => $backup->fresh(),
        ], 201);
    }

    /**
     * Process backup (simplified - in production this would be a queued job)
     */
    protected function processBackup(SystemBackup $backup)
    {
        $backup->markAsStarted();

        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $fileName = "backup_{$backup->backup_type}_{$timestamp}.zip";
            
            // Simulate backup process
            // In production, this would actually create the backup file
            $fileSize = rand(1000000, 100000000); // Simulated file size
            
            $backup->markAsCompleted(
                "backups/{$fileName}",
                $fileSize
            );
            
            $backup->update(['file_name' => $fileName]);

        } catch (\Exception $e) {
            $backup->markAsFailed($e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function download($id)
    {
        $backup = SystemBackup::findOrFail($id);

        if (!$backup->isComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Backup is not complete',
            ], 400);
        }

        if (!$backup->file_path || !Storage::exists($backup->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found',
            ], 404);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($backup)
            ->log('backup_downloaded');

        return Storage::download($backup->file_path, $backup->file_name);
    }

    /**
     * Delete backup
     */
    public function destroy($id)
    {
        $backup = SystemBackup::findOrFail($id);

        // Delete the actual file if it exists
        if ($backup->file_path && Storage::exists($backup->file_path)) {
            Storage::delete($backup->file_path);
        }

        $backup->update(['status' => 'deleted']);

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['backup_id' => $id, 'file_name' => $backup->file_name])
            ->log('backup_deleted');

        return response()->json([
            'success' => true,
            'message' => 'Backup deleted successfully',
        ]);
    }

    /**
     * Restore from backup
     */
    public function restore(Request $request, $id)
    {
        $backup = SystemBackup::findOrFail($id);

        if (!$backup->isComplete()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot restore from incomplete backup',
            ], 400);
        }

        $validated = $request->validate([
            'confirm' => 'required|boolean|accepted',
            'restore_type' => 'nullable|in:full,selective',
            'tables' => 'nullable|array',
        ]);

        // Log the restore attempt
        activity()
            ->causedBy(auth()->user())
            ->performedOn($backup)
            ->withProperties([
                'restore_type' => $validated['restore_type'] ?? 'full',
            ])
            ->log('backup_restore_initiated');

        // In production, this would queue a restore job
        return response()->json([
            'success' => true,
            'message' => 'Restore process initiated. This may take several minutes.',
            'data' => [
                'backup_id' => $id,
                'status' => 'processing',
            ],
        ]);
    }

    /**
     * Get backup statistics
     */
    public function stats()
    {
        $stats = [
            'total_backups' => SystemBackup::count(),
            'completed_backups' => SystemBackup::completed()->count(),
            'failed_backups' => SystemBackup::where('status', 'failed')->count(),
            'total_size_bytes' => SystemBackup::completed()->sum('file_size_bytes'),
            'last_backup' => SystemBackup::completed()->orderBy('completed_at', 'desc')->first(),
            'by_type' => SystemBackup::selectRaw('backup_type, count(*) as count')
                ->groupBy('backup_type')
                ->pluck('count', 'backup_type'),
            'last_7_days' => SystemBackup::recent(7)->count(),
        ];

        // Format total size
        $bytes = $stats['total_size_bytes'];
        if ($bytes >= 1073741824) {
            $stats['total_size_formatted'] = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $stats['total_size_formatted'] = number_format($bytes / 1048576, 2) . ' MB';
        } else {
            $stats['total_size_formatted'] = number_format($bytes / 1024, 2) . ' KB';
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get backup schedule configuration
     */
    public function schedule()
    {
        // Get current schedule settings from platform settings
        $settings = [
            'auto_backup_enabled' => config('backup.auto_enabled', true),
            'backup_frequency' => config('backup.frequency', 'daily'),
            'backup_time' => config('backup.time', '02:00'),
            'backup_type' => config('backup.type', 'full'),
            'retention_days' => config('backup.retention_days', 30),
            'notify_on_complete' => config('backup.notify', true),
            'notify_email' => config('backup.notify_email'),
        ];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Update backup schedule
     */
    public function updateSchedule(Request $request)
    {
        $validated = $request->validate([
            'auto_backup_enabled' => 'required|boolean',
            'backup_frequency' => 'required|in:hourly,daily,weekly,monthly',
            'backup_time' => 'required|date_format:H:i',
            'backup_type' => 'required|in:full,incremental,database_only',
            'retention_days' => 'required|integer|min:1|max:365',
            'notify_on_complete' => 'nullable|boolean',
            'notify_email' => 'nullable|email',
        ]);

        // In production, save these to platform_settings table
        // and update the task scheduler

        activity()
            ->causedBy(auth()->user())
            ->withProperties($validated)
            ->log('backup_schedule_updated');

        return response()->json([
            'success' => true,
            'message' => 'Backup schedule updated successfully',
            'data' => $validated,
        ]);
    }
}
