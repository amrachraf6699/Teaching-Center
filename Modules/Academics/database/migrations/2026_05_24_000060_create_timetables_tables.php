<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teaching_group_id')->unique()->constrained('teaching_groups')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('timetable_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->string('day_of_week', 16);
            $table->time('starts_at');
            $table->time('ends_at');
            $table->timestamps();

            $table->unique(['timetable_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
        Schema::dropIfExists('timetables');
    }
};
