<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Timetables table
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->integer('period');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('term')->nullable();
            $table->string('session')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'class_id']);
            $table->index(['school_id', 'teacher_id']);
        });

        // Duty Roster table
        Schema::create('duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('activity'); // assembly, sport, interhouse, religious, etc.
            $table->text('description')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'date']);
            $table->index(['school_id', 'teacher_id']);
        });

        // Non-Academic Performance table
        Schema::create('non_academic_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('term');
            $table->string('session');
            
            // Fixed metrics as per spec
            $table->integer('leadership')->default(3);
            $table->integer('honesty')->default(3);
            $table->integer('cooperation')->default(3);
            $table->integer('punctuality')->default(3);
            $table->integer('neatness')->default(3);
            
            $table->text('teacher_comment')->nullable();
            $table->text('principal_comment')->nullable();
            
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'term', 'session'], 'non_academic_index');
        });

        // Archive table (for storing read-only summaries of closed terms)
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('term');
            $table->string('session');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->longText('serialized_data'); // JSON blob of student performance
            $table->string('storage_path')->nullable(); // For PDF or Zip storage
            $table->timestamps();

            $table->index(['school_id', 'term', 'session']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archives');
        Schema::dropIfExists('non_academic_performance');
        Schema::dropIfExists('duties');
        Schema::dropIfExists('timetables');
    }
};
