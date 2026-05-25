<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_notifications', function (Blueprint $table): void {
            $table->foreignId('recipient_user_id')
                ->nullable()
                ->after('student_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('recipient_role')->default('parent')->after('recipient_user_id');
        });

        DB::table('parent_notifications')
            ->whereNull('recipient_user_id')
            ->update([
                'recipient_user_id' => DB::raw('parent_id'),
                'recipient_role' => 'parent',
            ]);
    }

    public function down(): void
    {
        Schema::table('parent_notifications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('recipient_user_id');
            $table->dropColumn('recipient_role');
        });
    }
};
