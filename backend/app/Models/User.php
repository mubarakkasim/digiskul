<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'school_id',
        'name',
        'email',
        'phone',
        'staff_id',
        'date_of_birth',
        'gender',
        'address',
        'qualification',
        'employment_date',
        'password',
        'role',
        'profile_photo',
        'meta',
        'last_login',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'employment_date' => 'date',
        'meta' => 'array',
        'last_login' => 'datetime',
        'active' => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get teacher assignments (classes and subjects assigned to this teacher)
     */
    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    /**
     * Get classes assigned to this teacher
     */
    public function assignedClasses()
    {
        return $this->belongsToMany(ClassModel::class, 'teacher_assignments', 'teacher_id', 'class_id')
            ->withPivot('subject_id', 'is_class_teacher', 'academic_session', 'term', 'active')
            ->wherePivot('active', true);
    }

    /**
     * Get class where this teacher is the class teacher
     */
    public function classTeacherOf()
    {
        return $this->belongsToMany(ClassModel::class, 'teacher_assignments', 'teacher_id', 'class_id')
            ->withPivot('is_class_teacher')
            ->wherePivot('is_class_teacher', true)
            ->wherePivot('active', true);
    }

    /**
     * Get students linked to this parent
     */
    public function linkedStudents()
    {
        return $this->belongsToMany(Student::class, 'parent_student_links', 'parent_id', 'student_id')
            ->withPivot('relationship', 'can_view_grades', 'can_view_attendance', 'can_view_fees', 'active')
            ->wherePivot('active', true);
    }

    /**
     * Get parent links for this user
     */
    public function parentLinks()
    {
        return $this->hasMany(ParentStudentLink::class, 'parent_id');
    }

    /**
     * Activity logs for this user
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get student profile if this user is a student
     */
    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    // ========================================
    // SCOPES
    // ========================================

    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeTeachers($query)
    {
        return $query->whereIn('role', ['teacher', 'class_teacher']);
    }

    public function scopeStaff($query)
    {
        return $query->whereIn('role', ['teacher', 'class_teacher', 'bursar', 'librarian', 'ict_officer']);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || $this->hasRole('super_admin');
    }

    /**
     * Check if user is a school admin
     */
    public function isSchoolAdmin(): bool
    {
        return $this->role === 'school_admin' || $this->hasRole('school_admin');
    }

    /**
     * Check if user is a teacher (any type)
     */
    public function isTeacher(): bool
    {
        return in_array($this->role, ['teacher', 'class_teacher']) || 
               $this->hasAnyRole(['teacher', 'class_teacher']);
    }

    /**
     * Check if user is a class teacher
     */
    public function isClassTeacher(): bool
    {
        return $this->role === 'class_teacher' || 
               $this->hasRole('class_teacher') ||
               $this->teacherAssignments()->where('is_class_teacher', true)->exists();
    }

    /**
     * Check if user is a student
     */
    public function isStudent(): bool
    {
        return $this->role === 'student' || $this->hasRole('student');
    }

    /**
     * Check if user is a parent
     */
    public function isParent(): bool
    {
        return $this->role === 'parent' || $this->hasRole('parent');
    }

    /**
     * Check if user can access a specific class
     */
    public function canAccessClass($classId): bool
    {
        // Super admin and school admin can access all classes
        if ($this->isSuperAdmin() || $this->isSchoolAdmin()) {
            return true;
        }

        // Teachers can only access assigned classes
        if ($this->isTeacher()) {
            return $this->assignedClasses()->where('classes.id', $classId)->exists();
        }

        // Students can access their own class
        if ($this->isStudent() && $this->studentProfile) {
            return $this->studentProfile->class_id == $classId;
        }

        // Parents can access classes their children are in
        if ($this->isParent()) {
            return $this->linkedStudents()->whereHas('classModel', function ($q) use ($classId) {
                $q->where('id', $classId);
            })->exists();
        }

        return false;
    }

    /**
     * Check if user can access a specific student's data
     */
    public function canAccessStudent($studentId): bool
    {
        // Super admin and school admin can access all students
        if ($this->isSuperAdmin() || $this->isSchoolAdmin()) {
            return true;
        }

        // Teachers can access students in their assigned classes
        if ($this->isTeacher()) {
            $student = Student::find($studentId);
            if ($student) {
                return $this->canAccessClass($student->class_id);
            }
        }

        // Students can only access their own data
        if ($this->isStudent() && $this->studentProfile) {
            return $this->studentProfile->id == $studentId;
        }

        // Parents can access their linked children
        if ($this->isParent()) {
            return $this->linkedStudents()->where('students.id', $studentId)->exists();
        }

        return false;
    }

    /**
     * Get dashboard route based on role
     */
    public function getDashboardRoute(): string
    {
        return match($this->role) {
            'super_admin' => '/super-admin/dashboard',
            'school_admin' => '/admin/dashboard',
            'teacher', 'class_teacher' => '/teacher/dashboard',
            'student' => '/student/dashboard',
            'parent' => '/parent/dashboard',
            'bursar' => '/bursar/dashboard',
            'librarian' => '/librarian/dashboard',
            'ict_officer' => '/ict/dashboard',
            default => '/dashboard',
        };
    }

    /**
     * Get full role name for display
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'school_admin' => 'School Administrator',
            'class_teacher' => 'Class Teacher',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent/Guardian',
            'bursar' => 'Bursar/Accountant',
            'librarian' => 'Librarian',
            'ict_officer' => 'ICT Officer',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }
}
