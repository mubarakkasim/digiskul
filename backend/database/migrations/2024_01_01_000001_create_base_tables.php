<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('subdomain')->unique();
            $table->string('domain')->nullable();
            $table->timestamp('license_valid_until')->nullable();
            $table->string('subscription_plan')->default('basic');
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('teacher');
            $table->string('profile_photo')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->boolean('active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->foreignId('teacher_incharge_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('section')->nullable();
            $table->string('level')->nullable();
            $table->foreignId('house_id')->nullable()->constrained('houses')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('admission_no')->unique();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->json('parent_info')->nullable();
            $table->json('health_info')->nullable();
            $table->string('previous_school')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('session');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('synced_at')->nullable();
            $table->string('device_tx_id')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'date']);
            $table->index(['student_id', 'date']);
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('term');
            $table->string('session');
            $table->decimal('score', 5, 2);
            $table->enum('assessment_type', ['ca1', 'ca2', 'exam'])->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->text('comment')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->string('device_tx_id')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'term', 'session']);
        });

        Schema::create('fee_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('components');
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
        });

        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_template_id')->constrained()->onDelete('cascade');
            $table->decimal('total_due', 10, 2);
            $table->json('scholarships')->nullable();
            $table->decimal('balance', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'card', 'mobile_money'])->default('cash');
            $table->string('trx_id')->nullable()->unique();
            $table->date('payment_date');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->string('receipt_no')->unique();
            $table->timestamp('synced_at')->nullable();
            $table->string('device_tx_id')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'payment_date']);
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('device_id');
            $table->string('operation_type');
            $table->json('payload');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('server_ts')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'device_id']);
        });

        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->foreignId('teacher_incharge_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('student_fees');
        Schema::dropIfExists('fee_templates');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('houses');
        Schema::dropIfExists('users');
        Schema::dropIfExists('schools');
    }
};

