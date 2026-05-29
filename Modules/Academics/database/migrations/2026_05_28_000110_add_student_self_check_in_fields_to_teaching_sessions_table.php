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
            $table->boolean('attendance_entry_enabled')->default(true)->after('session_date');
            $table->string('manual_attendance_code', 16)->nullable()->after('attendance_entry_enabled');
        });

        DB::table('teaching_sessions')
            ->select('id')
            ->orderBy('id')
            ->get()
            ->each(function (object $session): void {
                DB::table('teaching_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'manual_attendance_code' => 'SES-'.$session->id,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('teaching_sessions', function (Blueprint $table): void {
            $table->dropColumn([
                'attendance_entry_enabled',
                'manual_attendance_code',
            ]);
        });
    }
};
