<?php

namespace Modules\Exports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\Timetable;
use Modules\Exports\Support\TableExport;
use Modules\Exams\Models\Exam;
use Modules\People\Models\Student;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceExportController extends Controller
{
    public function parents(Request $request, string $format): SymfonyResponse
    {
        $this->ensureFormat($format);

        $rows = $this->parentsQuery($request)
            ->latest()
            ->get()
            ->map(fn (User $parent): array => [
                $parent->name,
                $parent->email,
                (string) $parent->children_count,
                $parent->created_at?->toFormattedDateString() ?? '-',
            ])
            ->all();

        return $this->download($format, 'parents-export', 'Parents Export', ['Name', 'Email', 'Children', 'Created'], $rows);
    }

    public function students(Request $request, string $format): SymfonyResponse
    {
        $this->ensureFormat($format);

        $rows = $this->studentsQuery($request)
            ->latest()
            ->get()
            ->map(fn (Student $student): array => [
                $student->name,
                $student->code,
                $student->parent?->name ?? '-',
                $student->groups->pluck('name')->join(', '),
                $student->phone ?? '-',
                $student->is_active ? 'Active' : 'Inactive',
                $student->created_at?->toFormattedDateString() ?? '-',
            ])
            ->all();

        return $this->download($format, 'students-export', 'Students Export', ['Student', 'Code', 'Parent', 'Groups', 'Phone', 'Status', 'Created'], $rows);
    }

    public function groups(Request $request, string $format): SymfonyResponse
    {
        $this->ensureFormat($format);

        $rows = $this->groupsQuery($request)
            ->latest()
            ->get()
            ->map(fn (TeachingGroup $group): array => [
                $group->name,
                $group->subject ?? '-',
                $group->level ?? '-',
                (string) $group->students_count,
                $group->is_active ? 'Active' : 'Inactive',
                $group->created_at?->toFormattedDateString() ?? '-',
            ])
            ->all();

        return $this->download($format, 'groups-export', 'Groups Export', ['Group', 'Subject', 'Level', 'Students', 'Status', 'Created'], $rows);
    }

    public function sessions(Request $request, string $format): SymfonyResponse
    {
        $this->ensureFormat($format);

        $rows = $this->sessionsQuery($request)
            ->latest('starts_at')
            ->get()
            ->map(fn (GroupSession $session): array => [
                $session->title,
                $session->group?->name ?? '-',
                $session->starts_at?->toDayDateTimeString() ?? '-',
                $session->ends_at?->toDayDateTimeString() ?? '-',
            ])
            ->all();

        return $this->download($format, 'sessions-export', 'Sessions Export', ['Session', 'Group', 'Starts', 'Ends'], $rows);
    }

    public function exams(Request $request, string $format): SymfonyResponse
    {
        $this->ensureFormat($format);

        $rows = $this->examsQuery($request)
            ->latest('start_at')
            ->get()
            ->map(fn (Exam $exam): array => [
                $exam->title,
                $exam->group?->name ?? '-',
                $this->scheduleSummary($exam),
                $this->allowedTimeLabel($exam->max_allowed_time),
                (string) $exam->questions_count,
                (string) $exam->max_score,
            ])
            ->all();

        return $this->download($format, 'exams-export', 'Exams Export', ['Exam', 'Group', 'Schedule', 'Allowed Time', 'Questions', 'Max Score'], $rows);
    }

    public function timetables(Request $request, string $format): StreamedResponse|HttpResponse
    {
        $this->ensureFormat($format);

        $timetables = $this->timetablesQuery($request)
            ->orderBy(
                TeachingGroup::query()
                    ->select('name')
                    ->whereColumn('teaching_groups.id', 'timetables.teaching_group_id')
                    ->limit(1)
            )
            ->get();

        $headers = collect($this->orderedWeekdayOptions())->pluck('label')->all();
        $rows = $this->exportScheduleGridRows($timetables);

        if ($format === 'csv') {
            return TableExport::csv('timetables-export.csv', $headers, $rows);
        }

        return TableExport::pdf('timetables-export.pdf', 'Timetables Export', $headers, $rows, 'landscape');
    }

    private function parentsQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $children = (string) $request->query('children', '');

        return User::query()
            ->where('role', 'parent')
            ->withCount('children')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($children === 'with', fn ($query) => $query->has('children'))
            ->when($children === 'without', fn ($query) => $query->doesntHave('children'));
    }

    private function studentsQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $parentId = (string) $request->query('parent_id', '');
        $groupId = (string) $request->query('group_id', '');

        return Student::query()
            ->with(['parent', 'groups'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhereHas('parent', fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->when($parentId !== '', fn ($query) => $query->where('parent_id', $parentId))
            ->when($groupId !== '', fn ($query) => $query->whereHas('groups', fn ($query) => $query->whereKey($groupId)));
    }

    private function groupsQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        return TeachingGroup::query()
            ->withCount('students')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhere('level', 'like', "%{$search}%")))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'));
    }

    private function sessionsQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $group = (string) $request->query('group', '');

        return GroupSession::query()
            ->with('group')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('group', fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%"))))
            ->when($group !== '', fn ($query) => $query->where('teaching_group_id', $group));
    }

    private function examsQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $group = (string) $request->query('group', '');

        return Exam::query()
            ->with('group')
            ->withCount('questions')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('group', fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%"))))
            ->when($group !== '', fn ($query) => $query->where('teaching_group_id', $group));
    }

    private function timetablesQuery(Request $request): Builder
    {
        $search = trim((string) $request->query('search', ''));
        $groupId = (string) $request->query('group_id', '');

        return Timetable::query()
            ->with(['group', 'entries'])
            ->when($search !== '', fn ($query) => $query->whereHas('group', fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")))
            ->when($groupId !== '', fn ($query) => $query->where('teaching_group_id', $groupId));
    }

    private function download(string $format, string $prefix, string $title, array $headers, array $rows): SymfonyResponse
    {
        $filename = $prefix.'-'.now()->format('Ymd_His').'.'.$format;

        if ($format === 'csv') {
            return TableExport::csv($filename, $headers, $rows);
        }

        return TableExport::pdf($filename, $title, $headers, $rows);
    }

    private function ensureFormat(string $format): void
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);
    }

    private function scheduleSummary(Exam $exam): string
    {
        if (! $exam->start_at || ! $exam->end_at) {
            return '-';
        }

        return $exam->start_at->format('M j, Y g:i A').' - '.$exam->end_at->format('g:i A');
    }

    private function allowedTimeLabel(?int $minutes): string
    {
        return $minutes ? $minutes.' min' : '-';
    }

    private function orderedWeekdayOptions(): array
    {
        return [
            ['value' => 'saturday', 'label' => 'Saturday'],
            ['value' => 'sunday', 'label' => 'Sunday'],
            ['value' => 'monday', 'label' => 'Monday'],
            ['value' => 'tuesday', 'label' => 'Tuesday'],
            ['value' => 'wednesday', 'label' => 'Wednesday'],
            ['value' => 'thursday', 'label' => 'Thursday'],
            ['value' => 'friday', 'label' => 'Friday'],
        ];
    }

    private function exportScheduleGridRows($timetables): array
    {
        $dayEntries = collect($this->orderedWeekdayOptions())
            ->mapWithKeys(fn (array $day): array => [$day['value'] => collect()]);

        foreach ($timetables as $timetable) {
            foreach ($timetable->entries as $entry) {
                if (! $dayEntries->has($entry->day_of_week)) {
                    continue;
                }

                $dayEntries[$entry->day_of_week]->push([
                    'starts_at' => $entry->starts_at,
                    'label' => ($timetable->group?->name ?? 'Unknown group').' ('.substr((string) $entry->starts_at, 0, 5).' - '.substr((string) $entry->ends_at, 0, 5).')',
                ]);
            }
        }

        $dayEntries = $dayEntries->map(fn ($entries) => $entries
            ->sortBy([['starts_at', 'asc'], ['label', 'asc']])
            ->pluck('label')
            ->values());

        $maxRows = max(1, ...$dayEntries->map->count()->values()->all());

        return collect(range(0, $maxRows - 1))
            ->map(fn (int $index): array => collect($this->orderedWeekdayOptions())
                ->map(fn (array $day): string => $dayEntries[$day['value']]->get($index, ''))
                ->all())
            ->all();
    }
}
