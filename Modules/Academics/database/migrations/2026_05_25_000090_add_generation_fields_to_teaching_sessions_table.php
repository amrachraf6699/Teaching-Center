<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teaching_sessions', function (Blueprint $table): void {
            $table->string('source_type', 20)->default('manual')->after('teaching_group_id');
            $table->foreignId('timetable_entry_id')->nullable()->after('source_type')->constrained('timetable_entries')->nullOnDelete();
            $table->date('session_date')->nullable()->after('ends_at');
        });

        DB::table('teaching_sessions')
            ->select(['id', 'starts_at'])
            ->orderBy('id')
            ->get()
            ->each(function (object $session): void {
                DB::table('teaching_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'session_date' => $session->starts_at ? substr((string) $session->starts_at, 0, 10) : now()->toDateString(),
                    ]);
            });

        Schema::table('teaching_sessions', function (Blueprint $table): void {
            $table->date('session_date')->nullable(false)->change();
            $table->index(['source_type', 'session_date']);
            $table->unique(['timetable_entry_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::table('teaching_sessions', function (Blueprint $table): void {
            $table->dropUnique(['timetable_entry_id', 'session_date']);
            $table->dropIndex(['source_type', 'session_date']);
            $table->dropConstrainedForeignId('timetable_entry_id');
            $table->dropColumn(['source_type', 'session_date']);
        });
    }
};
