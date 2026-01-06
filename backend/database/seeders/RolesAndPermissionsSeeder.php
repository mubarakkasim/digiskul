<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * DIGISKUL Complete Roles & Permissions Seeder
     * Implements comprehensive RBAC as per system specification
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ====================================================
        // PERMISSIONS - Grouped by Module
        // ====================================================

        $permissions = [
            // === SCHOOL MANAGEMENT (Super Admin Only) ===
            'schools.view_all',
            'schools.create',
            'schools.update',
            'schools.delete',
            'schools.suspend',
            'schools.manage_license',
            'schools.manage_subscription',
            'schools.view_analytics',
            'schools.manage_features',

            // === SYSTEM ADMINISTRATION (Super Admin Only) ===
            'system.view_logs',
            'system.manage_settings',
            'system.backup_restore',
            'system.manage_roles',
            'system.view_all_schools',
            'system.override_permissions',

            // === USER MANAGEMENT ===
            'users.view',
            'users.view_all',      // View all users in school
            'users.create',
            'users.update',
            'users.delete',
            'users.assign_roles',
            'users.manage_teachers',
            'users.manage_staff',

            // === STUDENT MANAGEMENT ===
            'students.view',
            'students.view_all',   // View all students in school
            'students.view_own_class', // View students in assigned class
            'students.create',
            'students.update',
            'students.delete',
            'students.export',
            'students.import',

            // === CLASS MANAGEMENT ===
            'classes.view',
            'classes.view_all',
            'classes.view_assigned', // View only assigned classes
            'classes.create',
            'classes.update',
            'classes.delete',
            'classes.assign_teachers',

            // === SUBJECT MANAGEMENT ===
            'subjects.view',
            'subjects.view_all',
            'subjects.view_assigned', // View only assigned subjects
            'subjects.create',
            'subjects.update',
            'subjects.delete',
            'subjects.assign_teachers',

            // === ATTENDANCE ===
            'attendance.view',
            'attendance.view_all',
            'attendance.view_own_class', // View attendance for assigned class
            'attendance.view_own',      // View own child's attendance (parent)
            'attendance.mark',
            'attendance.mark_own_class', // Mark attendance for assigned class only
            'attendance.update',
            'attendance.delete',
            'attendance.export',

            // === GRADES & ASSESSMENTS ===
            'grades.view',
            'grades.view_all',
            'grades.view_own_class',
            'grades.view_own_subject', // Teacher can view grades for their subject
            'grades.view_own',         // Student/Parent can view own grades
            'grades.record',
            'grades.record_own_subject', // Record grades only for assigned subjects
            'grades.update',
            'grades.delete',
            'grades.approve',          // Class teacher approves grades
            'grades.export',

            // === REPORT CARDS ===
            'report_cards.view',
            'report_cards.view_all',
            'report_cards.view_own_class',
            'report_cards.view_own',
            'report_cards.generate',
            'report_cards.approve',
            'report_cards.add_comments',
            'report_cards.download',
            'report_cards.print',

            // === TIMETABLE ===
            'timetable.view',
            'timetable.view_all',
            'timetable.view_own',
            'timetable.create',
            'timetable.update',
            'timetable.delete',
            'timetable.approve',
            'timetable.publish',

            // === DUTY ROSTER ===
            'duties.view',
            'duties.view_all',
            'duties.view_own',
            'duties.create',
            'duties.update',
            'duties.delete',
            'duties.assign',

            // === FEES & PAYMENTS ===
            'fees.view',
            'fees.view_all',
            'fees.view_own',
            'fees.create',
            'fees.update',
            'fees.delete',
            'fees.manage_templates',
            'payments.view',
            'payments.view_all',
            'payments.view_own',
            'payments.record',
            'payments.update',
            'payments.delete',
            'payments.generate_receipt',
            'payments.export',
            'debtors.view',
            'debtors.remind',

            // === REPORTS & ANALYTICS ===
            'reports.view',
            'reports.view_all',
            'reports.view_own_class',
            'reports.generate',
            'reports.export',
            'analytics.view',
            'analytics.view_class',

            // === ARCHIVE ===
            'archive.view',
            'archive.create',
            'archive.download',
            'archive.delete',
            'archive.manage',

            // === SETTINGS ===
            'settings.view',
            'settings.update',
            'settings.manage_school',
            'settings.manage_terms',
            'settings.manage_sessions',

            // === ANNOUNCEMENTS ===
            'announcements.view',
            'announcements.view_all',
            'announcements.create',
            'announcements.update',
            'announcements.delete',
            'announcements.publish',

            // === NON-ACADEMIC PERFORMANCE ===
            'non_academic.view',
            'non_academic.view_all',
            'non_academic.view_own_class',
            'non_academic.view_own',
            'non_academic.record',
            'non_academic.update',

            // === LIBRARY (Optional Module) ===
            'library.view',
            'library.manage',
            'library.issue_books',
            'library.return_books',

            // === AI FEATURES ===
            'ai.generate_comments',
            'ai.view_insights',
            'ai.manage_settings',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // ====================================================
        // ROLES CREATION WITH PERMISSIONS
        // ====================================================

        // === 1. SUPER ADMIN - System Owner ===
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all()); // Full access

        // === 2. SCHOOL ADMIN / PRINCIPAL ===
        $schoolAdmin = Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
        $schoolAdmin->syncPermissions([
            // User Management
            'users.view', 'users.view_all', 'users.create', 'users.update', 'users.delete',
            'users.assign_roles', 'users.manage_teachers', 'users.manage_staff',
            
            // Student Management
            'students.view', 'students.view_all', 'students.create', 'students.update',
            'students.delete', 'students.export', 'students.import',
            
            // Class & Subject Management
            'classes.view', 'classes.view_all', 'classes.create', 'classes.update',
            'classes.delete', 'classes.assign_teachers',
            'subjects.view', 'subjects.view_all', 'subjects.create', 'subjects.update',
            'subjects.delete', 'subjects.assign_teachers',
            
            // Attendance
            'attendance.view', 'attendance.view_all', 'attendance.mark', 'attendance.update',
            'attendance.delete', 'attendance.export',
            
            // Grades
            'grades.view', 'grades.view_all', 'grades.record', 'grades.update',
            'grades.delete', 'grades.approve', 'grades.export',
            
            // Report Cards
            'report_cards.view', 'report_cards.view_all', 'report_cards.generate',
            'report_cards.approve', 'report_cards.add_comments', 'report_cards.download', 'report_cards.print',
            
            // Timetable & Duties
            'timetable.view', 'timetable.view_all', 'timetable.create', 'timetable.update',
            'timetable.delete', 'timetable.approve', 'timetable.publish',
            'duties.view', 'duties.view_all', 'duties.create', 'duties.update',
            'duties.delete', 'duties.assign',
            
            // Fees & Payments
            'fees.view', 'fees.view_all', 'fees.create', 'fees.update', 'fees.delete',
            'fees.manage_templates',
            'payments.view', 'payments.view_all', 'payments.record', 'payments.update',
            'payments.delete', 'payments.generate_receipt', 'payments.export',
            'debtors.view', 'debtors.remind',
            
            // Reports & Analytics
            'reports.view', 'reports.view_all', 'reports.generate', 'reports.export',
            'analytics.view', 'analytics.view_class',
            
            // Archive & Settings
            'archive.view', 'archive.create', 'archive.download', 'archive.delete', 'archive.manage',
            'settings.view', 'settings.update', 'settings.manage_school',
            'settings.manage_terms', 'settings.manage_sessions',
            
            // Announcements
            'announcements.view', 'announcements.view_all', 'announcements.create',
            'announcements.update', 'announcements.delete', 'announcements.publish',
            
            // Non-Academic & AI
            'non_academic.view', 'non_academic.view_all', 'non_academic.record', 'non_academic.update',
            'ai.generate_comments', 'ai.view_insights', 'ai.manage_settings',
        ]);

        // === 3. TEACHER - Basic Teacher Role ===
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->syncPermissions([
            // Limited User View
            'users.view', // View own profile
            
            // Students - Only assigned classes
            'students.view', 'students.view_own_class',
            
            // Classes & Subjects - Only assigned
            'classes.view', 'classes.view_assigned',
            'subjects.view', 'subjects.view_assigned',
            
            // Attendance - Mark for assigned classes
            'attendance.view', 'attendance.view_own_class', 'attendance.mark_own_class',
            
            // Grades - Record for assigned subjects
            'grades.view', 'grades.view_own_subject', 'grades.record_own_subject',
            
            // Report Cards - View for assigned classes
            'report_cards.view', 'report_cards.view_own_class', 'report_cards.add_comments',
            
            // Timetable & Duties - View own
            'timetable.view', 'timetable.view_own',
            'duties.view', 'duties.view_own',
            
            // Reports - Class level
            'reports.view', 'reports.view_own_class',
            'analytics.view_class',
            
            // Announcements - View only
            'announcements.view',
            
            // Non-Academic - Record for assigned classes
            'non_academic.view', 'non_academic.view_own_class', 'non_academic.record',
            
            // AI Features
            'ai.generate_comments',
        ]);

        // === 4. CLASS TEACHER - Extended Teacher Role ===
        $classTeacher = Role::firstOrCreate(['name' => 'class_teacher', 'guard_name' => 'web']);
        $classTeacher->syncPermissions([
            // All teacher permissions plus:
            'users.view',
            'students.view', 'students.view_own_class', 'students.update', // Can update student info
            'classes.view', 'classes.view_assigned', 'classes.view_all', // Full class view
            'subjects.view', 'subjects.view_assigned', 'subjects.view_all',
            
            // Enhanced Attendance
            'attendance.view', 'attendance.view_own_class', 'attendance.mark_own_class',
            'attendance.view_all', // Can see all attendance for coordination
            
            // Enhanced Grades - Can view all for class coordination
            'grades.view', 'grades.view_own_class', 'grades.record_own_subject',
            'grades.approve', // Can approve grades for their class
            
            // Full Report Cards Access for Class
            'report_cards.view', 'report_cards.view_own_class', 'report_cards.generate',
            'report_cards.add_comments', 'report_cards.download', 'report_cards.print',
            
            // Timetable & Duties
            'timetable.view', 'timetable.view_own', 'timetable.view_all',
            'duties.view', 'duties.view_own', 'duties.view_all',
            
            // Enhanced Reports
            'reports.view', 'reports.view_own_class', 'reports.generate', 'reports.export',
            'analytics.view', 'analytics.view_class',
            
            // Announcements
            'announcements.view',
            
            // Non-Academic - Full class access
            'non_academic.view', 'non_academic.view_own_class', 'non_academic.record', 'non_academic.update',
            
            // AI Features
            'ai.generate_comments', 'ai.view_insights',
        ]);

        // === 5. STUDENT ===
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            // Read-only access to own data
            'students.view', // View own profile
            'attendance.view_own',
            'grades.view_own',
            'report_cards.view_own', 'report_cards.download',
            'timetable.view_own',
            'fees.view_own',
            'payments.view_own',
            'announcements.view',
            'non_academic.view_own',
        ]);

        // === 6. PARENT / GUARDIAN ===
        $parent = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $parent->syncPermissions([
            // Read-only access to linked children's data
            'students.view', // View linked children profiles
            'attendance.view_own', // View linked children's attendance
            'grades.view_own', // View linked children's grades
            'report_cards.view_own', 'report_cards.download',
            'fees.view_own',
            'payments.view_own',
            'announcements.view',
            'non_academic.view_own',
        ]);

        // === 7. BURSAR / ACCOUNTANT ===
        $bursar = Role::firstOrCreate(['name' => 'bursar', 'guard_name' => 'web']);
        $bursar->syncPermissions([
            'users.view',
            'students.view', 'students.view_all', // Need to view students for fee assignment
            
            // Full Fees & Payments Access
            'fees.view', 'fees.view_all', 'fees.create', 'fees.update', 'fees.delete',
            'fees.manage_templates',
            'payments.view', 'payments.view_all', 'payments.record', 'payments.update',
            'payments.delete', 'payments.generate_receipt', 'payments.export',
            'debtors.view', 'debtors.remind',
            
            // Reports
            'reports.view', 'reports.generate', 'reports.export',
            
            // Announcements
            'announcements.view',
        ]);

        // === 8. LIBRARIAN ===
        $librarian = Role::firstOrCreate(['name' => 'librarian', 'guard_name' => 'web']);
        $librarian->syncPermissions([
            'users.view',
            'students.view', 'students.view_all',
            'library.view', 'library.manage', 'library.issue_books', 'library.return_books',
            'announcements.view',
        ]);

        // === 9. ICT OFFICER ===
        $ictOfficer = Role::firstOrCreate(['name' => 'ict_officer', 'guard_name' => 'web']);
        $ictOfficer->syncPermissions([
            'users.view', 'users.view_all', 'users.create', 'users.update', // User support
            'students.view', 'students.view_all', // Student support
            'settings.view',
            'announcements.view',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                ['super_admin', Permission::count()],
                ['school_admin', $schoolAdmin->permissions->count()],
                ['class_teacher', $classTeacher->permissions->count()],
                ['teacher', $teacher->permissions->count()],
                ['student', $student->permissions->count()],
                ['parent', $parent->permissions->count()],
                ['bursar', $bursar->permissions->count()],
                ['librarian', $librarian->permissions->count()],
                ['ict_officer', $ictOfficer->permissions->count()],
            ]
        );
    }
}
