<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_type',
        'school_id',
        'file_path',
        'file_name',
        'file_size_bytes',
        'status',
        'started_at',
        'completed_at',
        'created_by',
        'notes',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Get the school for this backup (if school-specific)
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user who created this backup
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes ?? 0;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' B';
    }

    /**
     * Get backup duration
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $diff = $this->started_at->diff($this->completed_at);
        
        if ($diff->h > 0) {
            return $diff->format('%h hours %i minutes');
        } elseif ($diff->i > 0) {
            return $diff->format('%i minutes %s seconds');
        }
        
        return $diff->format('%s seconds');
    }

    /**
     * Check if backup is complete
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for completed backups
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for recent backups
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark as started
     */
    public function markAsStarted(): self
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(string $filePath, int $fileSize): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'file_path' => $filePath,
            'file_size_bytes' => $fileSize,
        ]);

        return $this;
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $errorMessage): self
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);

        return $this;
    }
}
