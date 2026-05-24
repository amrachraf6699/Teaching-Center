<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\People\Models\Student;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->unique()->after('parent_id')->constrained('users')->nullOnDelete();
        });

        Student::query()
            ->whereNull('user_id')
            ->orderBy('id')
            ->get()
            ->each(function (Student $student): void {
                $user = User::query()->create([
                    'name' => $student->name,
                    'email' => sprintf('student-%d@students.teachify.local', $student->id),
                    'password' => Hash::make($student->code ?: 'password'),
                    'role' => 'student',
                ]);

                $student->forceFill([
                    'user_id' => $user->id,
                ])->save();
            });
    }

    public function down(): void
    {
        $studentUserIds = DB::table('students')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        Schema::table('students', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });

        if ($studentUserIds !== []) {
            User::query()
                ->whereIn('id', $studentUserIds)
                ->where('role', 'student')
                ->delete();
        }
    }
};
