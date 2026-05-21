<?php

namespace Modules\People\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\People\Models\Student;

class PeopleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parents = User::query()
            ->whereIn('email', [
                'mona.parent@teachify.test',
                'karim.parent@teachify.test',
                'sara.parent@teachify.test',
            ])
            ->get()
            ->keyBy('email');

        collect([
            ['parent' => 'mona.parent@teachify.test', 'name' => 'Omar Hassan', 'code' => 'ST-001', 'phone' => '01000000001'],
            ['parent' => 'mona.parent@teachify.test', 'name' => 'Laila Hassan', 'code' => 'ST-002', 'phone' => '01000000002'],
            ['parent' => 'karim.parent@teachify.test', 'name' => 'Youssef Saleh', 'code' => 'ST-003', 'phone' => '01000000003'],
            ['parent' => 'karim.parent@teachify.test', 'name' => 'Nour Saleh', 'code' => 'ST-004', 'phone' => '01000000004'],
            ['parent' => 'sara.parent@teachify.test', 'name' => 'Adam Nabil', 'code' => 'ST-005', 'phone' => '01000000005'],
            ['parent' => 'sara.parent@teachify.test', 'name' => 'Mariam Nabil', 'code' => 'ST-006', 'phone' => '01000000006'],
        ])->each(function (array $student) use ($parents): void {
            Student::updateOrCreate(
                ['code' => $student['code']],
                Student::factory()
                    ->forParent($parents[$student['parent']])
                    ->make([
                        'name' => $student['name'],
                        'code' => $student['code'],
                        'phone' => $student['phone'],
                        'notes' => 'Seeded demo student.',
                        'is_active' => true,
                    ])
                    ->getAttributes(),
            );
        });
    }
}
