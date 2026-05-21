<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Academics\Database\Seeders\AcademicsDatabaseSeeder;
use Modules\Exams\Database\Seeders\ExamsDatabaseSeeder;
use Modules\Notifications\Database\Seeders\NotificationsDatabaseSeeder;
use Modules\People\Database\Seeders\PeopleDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TeachifyUsersSeeder::class,
            PeopleDatabaseSeeder::class,
            AcademicsDatabaseSeeder::class,
            ExamsDatabaseSeeder::class,
            NotificationsDatabaseSeeder::class,
            ApplicationSupportSeeder::class,
        ]);

        $this->command?->info('Seeded Teachify demo data.');
        $this->command?->line('Teacher login: teacher@teachify.test / password');

        User::query()
            ->where('role', 'parent')
            ->orderBy('email')
            ->pluck('email')
            ->each(fn (string $email): int => $this->command?->line("Parent login: {$email} / password") ?? 0);
    }
}
