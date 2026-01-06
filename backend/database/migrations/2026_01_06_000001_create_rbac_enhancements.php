<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - DIGISKUL RBAC Enhancement
     * Creates tables for comprehensive role-based access control
     */
    public function up(): void
    {
        // Teacher-Class Assignments (which class/subjects a teacher can access)
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            $table->boolean('is_class_teacher')->default(false);
            $table->string('academic_session')->nullable();
            $table->string('term')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['teacher_id', 'class_id', 'subject_id'], 'unique_teacher_class_subject');
            $table->index(['school_id', 'teacher_id']);
            $table->index(['school_id', 'class_id']);
        });

        // Parent-Student Links (which students a parent can view)
        Schema::create('parent_student_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('relationship')->default('parent'); // parent, guardian, sponsor
            $table->boolean('can_view_grades')->default(true);
            $table->boolean('can_view_attendance')->default(true);
            $table->boolean('can_view_fees')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['parent_id', 'student_id']);
            $table->index(['school_id', 'parent_id']);
        });

        // Student Users (link students to user accounts for student portal)
        Schema::create('student_user_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'student_id']);
        });

        // Activity Logs (NDPR-compliant activity tracking)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // login, logout, create, update, delete, view, export
            $table->string('entity_type')->nullable(); // student, grade, attendance, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'user_id']);
            $table->index(['school_id', 'action']);
            $table->index(['school_id', 'created_at']);
        });

        // System Settings (for Super Admin configuration)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, json, boolean, integer
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // School Feature Toggles (Super Admin can enable/disable features per school)
        Schema::create('school_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('feature'); // e.g., 'attendance', 'fees', 'ai_comments', 'duty_roster'
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'feature']);
        });

        // Non-Academic Staff Modules (for accountant, librarian, etc.)
        Schema::create('staff_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('module'); // fees, library, ict_support
            $table->json('permissions')->nullable(); // module-specific permissions
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'module']);
            $table->index(['school_id', 'module']);
        });

        // School Announcements (for notices visible to specific roles)
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->json('target_roles'); // ['student', 'parent', 'teacher']
            $table->boolean('is_global')->default(false); // Super admin system-wide
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'active', 'published_at']);
        });

        // Consent Records (parent acknowledgments)
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('announcement_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('consent_type'); // announcement_ack, policy_ack, permission_slip
            $table->boolean('consented')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'parent_id']);
        });

        // Add user_id to students table for student portal login
        if (!Schema::hasColumn('students', 'user_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('school_id')->constrained()->onDelete('set null');
                $table->foreignId('house_id')->nullable()->after('class_id')->constrained('houses')->onDelete('set null');
                $table->date('date_of_birth')->nullable()->after('full_name');
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
                $table->string('photo')->nullable()->after('gender');
                $table->boolean('active')->default(true)->after('meta');
            });
        }

        // Add additional user fields for enhanced role management
        if (!Schema::hasColumn('users', 'staff_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('staff_id')->nullable()->after('email');
                $table->date('date_of_birth')->nullable()->after('phone');
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
                $table->text('address')->nullable()->after('gender');
                $table->string('qualification')->nullable()->after('address');
                $table->date('employment_date')->nullable()->after('qualification');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_records');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('staff_modules');
        Schema::dropIfExists('school_features');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('student_user_links');
        Schema::dropIfExists('parent_student_links');
        Schema::dropIfExists('teacher_assignments');

        // Remove added columns
        if (Schema::hasColumn('students', 'user_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn(['user_id', 'house_id', 'date_of_birth', 'gender', 'photo', 'active']);
            });
        }

        if (Schema::hasColumn('users', 'staff_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['staff_id', 'date_of_birth', 'gender', 'address', 'qualification', 'employment_date']);
            });
        }
    }
};
