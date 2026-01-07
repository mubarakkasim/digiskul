<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Super Admin Panel Database Tables
     * Based on SUPER_ADMIN_PANEL_SPECIFICATION.md
     */
    public function up(): void
    {
        // ============================================
        // 1. LICENSE PLANS
        // ============================================
        Schema::create('license_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->integer('duration_months')->default(12);
            $table->integer('user_limit')->nullable();
            $table->integer('student_limit')->nullable();
            $table->decimal('storage_gb', 10, 2)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 3)->default('NGN');
            $table->json('features')->nullable();
            $table->integer('trial_days')->default(0);
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        // ============================================
        // 2. SCHOOL SUBSCRIPTIONS
        // ============================================
        Schema::create('school_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('license_plan_id')->constrained()->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'grace_period', 'trial'])->default('active');
            $table->boolean('auto_renew')->default(false);
            $table->string('payment_reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('amount_paid', 12, 2)->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index('end_date');
        });

        // ============================================
        // 3. PLATFORM SETTINGS (Enhanced)
        // ============================================
        if (!Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key_name', 100)->unique();
                $table->text('value')->nullable();
                $table->string('category', 50)->default('general');
                $table->string('type', 20)->default('string');
                $table->boolean('is_encrypted')->default(false);
                $table->text('description')->nullable();
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index('category');
            });
        }

        // ============================================
        // 4. SYSTEM BACKUPS
        // ============================================
        Schema::create('system_backups', function (Blueprint $table) {
            $table->id();
            $table->enum('backup_type', ['full', 'incremental', 'school_only', 'database_only', 'files_only'])->default('full');
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->bigInteger('file_size_bytes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'deleted'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('school_id');
        });

        // ============================================
        // 5. SYSTEM ANNOUNCEMENTS (Platform-wide)
        // ============================================
        Schema::create('system_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('content');
            $table->enum('priority', ['normal', 'important', 'critical'])->default('normal');
            $table->enum('target_type', ['all', 'specific_schools', 'specific_roles', 'specific_plans'])->default('all');
            $table->json('target_ids')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_dismissible')->default(true);
            $table->boolean('show_on_login')->default(false);
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['is_published', 'publish_at', 'expires_at']);
        });

        // ============================================
        // 6. IMPERSONATION LOGS (Security Audit)
        // ============================================
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('super_admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('impersonated_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('set null');
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->json('actions_performed')->nullable();
            $table->timestamps();

            $table->index(['super_admin_id', 'started_at']);
        });

        // ============================================
        // 7. MAINTENANCE MODE HISTORY
        // ============================================
        Schema::create('maintenance_history', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['enabled', 'disabled']);
            $table->text('message')->nullable();
            $table->json('bypass_ips')->nullable();
            $table->json('bypass_users')->nullable();
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->timestamps();
        });

        // ============================================
        // 8. FEATURE REGISTRY (Global Feature Definitions)
        // ============================================
        Schema::create('feature_registry', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->default('general');
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->json('dependencies')->nullable();
            $table->json('plans')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });

        // ============================================
        // 9. SYSTEM HEALTH METRICS
        // ============================================
        Schema::create('system_health_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type', 50);
            $table->string('metric_name', 100);
            $table->decimal('value', 15, 4);
            $table->string('unit', 20)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['metric_type', 'recorded_at']);
            $table->index('recorded_at');
        });

        // ============================================
        // 10. ALERT RULES
        // ============================================
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('metric_type', 50);
            $table->string('condition', 20);
            $table->decimal('threshold', 15, 4);
            $table->integer('duration_minutes')->default(5);
            $table->json('notification_channels');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // ============================================
        // 11. ALERT HISTORY
        // ============================================
        Schema::create('alert_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_rule_id')->constrained()->onDelete('cascade');
            $table->string('severity', 20);
            $table->text('message');
            $table->decimal('metric_value', 15, 4);
            $table->enum('status', ['triggered', 'acknowledged', 'resolved'])->default('triggered');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // ============================================
        // 12. Update Schools table with additional fields
        // ============================================
        if (!Schema::hasColumn('schools', 'status')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->enum('status', ['active', 'trial', 'suspended', 'pending', 'archived'])->default('active')->after('active');
                $table->string('registration_number')->nullable()->after('subdomain');
                $table->string('country', 100)->nullable()->after('address');
                $table->string('state', 100)->nullable()->after('country');
                $table->string('city', 100)->nullable()->after('state');
                $table->string('timezone')->default('Africa/Lagos')->after('city');
                $table->date('trial_ends_at')->nullable()->after('license_valid_until');
                $table->json('settings')->nullable()->after('meta');
                $table->json('branding')->nullable()->after('settings');
                $table->bigInteger('storage_used_bytes')->default(0)->after('branding');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_history');
        Schema::dropIfExists('alert_rules');
        Schema::dropIfExists('system_health_metrics');
        Schema::dropIfExists('feature_registry');
        Schema::dropIfExists('maintenance_history');
        Schema::dropIfExists('impersonation_logs');
        Schema::dropIfExists('system_announcements');
        Schema::dropIfExists('system_backups');
        
        if (Schema::hasTable('platform_settings')) {
            Schema::dropIfExists('platform_settings');
        }
        
        Schema::dropIfExists('school_subscriptions');
        Schema::dropIfExists('license_plans');

        // Remove added columns from schools table
        if (Schema::hasColumn('schools', 'status')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->dropColumn([
                    'status', 'registration_number', 'country', 'state', 
                    'city', 'timezone', 'trial_ends_at', 'settings', 
                    'branding', 'storage_used_bytes'
                ]);
            });
        }
    }
};
