<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->string('student_review_mode', 32)->default('score_only')->after('notes');
        });

        Schema::create('exam_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at');
            $table->string('status', 32)->default('in_progress');
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });

        Schema::create('exam_attempt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('exam_question_options')->nullOnDelete();
            $table->boolean('is_correct')->nullable();
            $table->decimal('earned_points', 8, 2)->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'exam_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_answers');
        Schema::dropIfExists('exam_attempts');

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn('student_review_mode');
        });
    }
};
