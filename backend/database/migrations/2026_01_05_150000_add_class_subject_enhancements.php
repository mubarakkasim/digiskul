<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add arm, teacher_id, and description to classes table
        Schema::table('classes', function (Blueprint $table) {
            if (!Schema::hasColumn('classes', 'arm')) {
                $table->string('arm')->nullable()->after('level');
            }
            if (!Schema::hasColumn('classes', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('arm')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('classes', 'description')) {
                $table->text('description')->nullable()->after('teacher_id');
            }
        });

        // Add type and description to subjects table
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'type')) {
                $table->enum('type', ['core', 'elective'])->default('core')->after('code');
            }
            if (!Schema::hasColumn('subjects', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
        });

        // Create class_subject pivot table if it doesn't exist
        if (!Schema::hasTable('class_subject')) {
            Schema::create('class_subject', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
                $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['class_id', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn(['arm', 'description']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['type', 'description']);
        });

        Schema::dropIfExists('class_subject');
    }
};
