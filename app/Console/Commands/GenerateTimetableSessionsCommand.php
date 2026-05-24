<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Academics\Actions\GenerateSessionsFromTimetables;

class GenerateTimetableSessionsCommand extends Command
{
    protected $signature = 'sessions:generate-from-timetables';

    protected $description = 'Generate the next 7 days of session instances from active timetables.';

    public function handle(GenerateSessionsFromTimetables $generator): int
    {
        $generator->handle(windowStart: now(), days: 7);

        $this->info('Timetable sessions generated successfully.');

        return self::SUCCESS;
    }
}
