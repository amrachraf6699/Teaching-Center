<?php

namespace Modules\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Services\PortalNotificationService;
use Modules\People\Models\Student;

class NotificationsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notifications = app(PortalNotificationService::class);

        Student::query()->orderBy('code')->get()->each(function (Student $student) use ($notifications): void {
            $notifications->createOnceForAudience(
                $student,
                'parent',
                'general',
                sprintf('seed:welcome:%d', $student->id),
                'Welcome to Teachify',
                "The parent portal is ready for {$student->name}.",
            );
        });
    }
}
