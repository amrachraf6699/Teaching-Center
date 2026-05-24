<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->dateTime('start_at')->nullable()->after('title');
            $table->dateTime('end_at')->nullable()->after('start_at');
            $table->unsignedInteger('max_allowed_time')->default(60)->after('max_score');
        });

        DB::table('exams')
            ->select(['id', 'exam_date'])
            ->orderBy('id')
            ->get()
            ->each(function (object $exam): void {
                $date = $exam->exam_date ?: now()->toDateString();

                DB::table('exams')
                    ->where('id', $exam->id)
                    ->update([
                        'start_at' => $date.' 09:00:00',
                        'end_at' => $date.' 10:00:00',
                        'max_allowed_time' => 60,
                    ]);
            });

        Schema::create('exam_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('type', 32);
            $table->text('prompt');
            $table->decimal('points', 8, 2);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('exam_question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->string('label');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn('exam_date');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table): void {
            $table->date('exam_date')->nullable()->after('title');
        });

        DB::table('exams')
            ->select(['id', 'start_at'])
            ->orderBy('id')
            ->get()
            ->each(function (object $exam): void {
                DB::table('exams')
                    ->where('id', $exam->id)
                    ->update([
                        'exam_date' => $exam->start_at ? substr((string) $exam->start_at, 0, 10) : now()->toDateString(),
                    ]);
            });

        Schema::dropIfExists('exam_question_options');
        Schema::dropIfExists('exam_questions');

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn(['start_at', 'end_at', 'max_allowed_time']);
        });
    }
};
