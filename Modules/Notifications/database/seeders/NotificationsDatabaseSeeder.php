<?php

namespace Modules\Notifications\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notifications\Models\ParentNotification;
use Modules\People\Models\Student;

class NotificationsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::query()->orderBy('code')->get()->each(function (Student $student): void {
            ParentNotification::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'type' => 'general',
                    'title' => 'Welcome to Teachify',
                ],
                ParentNotification::factory()->forStudent($student)->make([
                    'type' => 'general',
                    'title' => 'Welcome to Teachify',
                    'body' => "The parent portal is ready for {$student->name}.",
                    'read_at' => null,
                    'emailed_at' => now(),
                ])->getAttributes(),
            );
        });
    }
}
