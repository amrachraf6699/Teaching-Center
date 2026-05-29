<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Actions\GenerateSessionsFromTimetables;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;
use Modules\Academics\Models\Timetable;
use Modules\Academics\Models\TimetableEntry;

class TimetableController extends Controller
{
    public function __construct(private readonly GenerateSessionsFromTimetables $generateSessions) {}

    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('Admin/Timetables/Index', [
            'timetables' => $this->filteredIndexQuery($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (Timetable $timetable): array => [
                    'id' => $timetable->id,
                    'group_name' => $timetable->group?->name ?? '-',
                    'subject' => $timetable->group?->subject ?? '-',
                    'active_days_count' => $timetable->entries->count(),
                    'weekly_summary' => $timetable->entries
                        ->map(fn ($entry): string => $this->labelForDay($entry->day_of_week).' '.$this->formatTime($entry->starts_at).' - '.$this->formatTime($entry->ends_at))
                        ->join(', '),
                    'show_url' => route('admin.timetables.show', $timetable),
                    'edit_url' => route('admin.timetables.edit', $timetable),
                    'delete_url' => route('admin.timetables.destroy', $timetable),
                    'created_at' => $timetable->created_at?->toFormattedDateString(),
                ]),
            'filters' => $filters,
            'groupOptions' => TeachingGroup::query()
                ->orderBy('name')
                ->get(['id', 'name', 'subject'])
                ->map(fn (TeachingGroup $group): array => [
                    'value' => (string) $group->id,
                    'label' => $group->name,
                    'description' => $group->subject,
                ])->values()->all(),
            'indexUrl' => route('admin.timetables.index'),
            'createUrl' => route('admin.timetables.create'),
            'exportUrls' => [
                'csv' => route('admin.timetables.export', 'csv'),
                'pdf' => route('admin.timetables.export', 'pdf'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Timetables/Create', [
            'groups' => TeachingGroup::query()
                ->doesntHave('timetable')
                ->orderBy('name')
                ->get(['id', 'name', 'subject'])
                ->map(fn (TeachingGroup $group): array => [
                    'value' => (string) $group->id,
                    'label' => $group->name,
                    'description' => $group->subject,
                ])->values()->all(),
            'weekdays' => $this->weekdayOptions(),
            'action' => route('admin.timetables.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id', 'unique:timetables,teaching_group_id'],
            'entries' => ['required', 'array', 'size:7'],
            'entries.*.day' => ['required', 'string'],
            'entries.*.active' => ['boolean'],
            'entries.*.starts_at' => ['nullable', 'date_format:H:i'],
            'entries.*.ends_at' => ['nullable', 'date_format:H:i'],
        ]);

        $activeEntries = $this->normalizeEntries($data['entries']);
        $this->ensureNoScheduleConflicts($activeEntries);

        $timetable = Timetable::create([
            'teaching_group_id' => $data['teaching_group_id'],
        ]);

        $timetable->entries()->createMany($activeEntries);
        $this->generateSessions->handle($timetable->load(['group', 'entries']), now(), 7);

        return redirect()->route('admin.timetables.index')->with('status', 'Timetable created.');
    }

    public function show(Timetable $timetable): Response
    {
        $timetable->load(['group', 'entries']);

        return Inertia::render('Admin/Timetables/Show', [
            'timetable' => [
                'id' => $timetable->id,
                'group' => $timetable->group ? [
                    'id' => $timetable->group->id,
                    'name' => $timetable->group->name,
                    'subject' => $timetable->group->subject,
                    'show_url' => route('admin.groups.show', $timetable->group),
                ] : null,
                'entries' => $timetable->entries->map(fn ($entry): array => [
                    'day_label' => $this->labelForDay($entry->day_of_week),
                    'time_range' => $this->formatTime($entry->starts_at).' - '.$this->formatTime($entry->ends_at),
                ])->values()->all(),
                'edit_url' => route('admin.timetables.edit', $timetable),
                'index_url' => route('admin.timetables.index'),
                'created_at' => $timetable->created_at?->toDayDateTimeString(),
            ],
        ]);
    }

    public function edit(Timetable $timetable): Response
    {
        $timetable->load(['group', 'entries']);

        return Inertia::render('Admin/Timetables/Edit', [
            'timetable' => [
                'id' => $timetable->id,
                'group' => $timetable->group ? [
                    'id' => $timetable->group->id,
                    'name' => $timetable->group->name,
                    'subject' => $timetable->group->subject,
                ] : null,
                'entries' => $this->entriesForForm($timetable),
            ],
            'weekdays' => $this->weekdayOptions(),
            'action' => route('admin.timetables.update', $timetable),
            'showUrl' => route('admin.timetables.show', $timetable),
        ]);
    }

    public function update(Request $request, Timetable $timetable): RedirectResponse
    {
        $data = $request->validate([
            'entries' => ['required', 'array', 'size:7'],
            'entries.*.day' => ['required', 'string'],
            'entries.*.active' => ['boolean'],
            'entries.*.starts_at' => ['nullable', 'date_format:H:i'],
            'entries.*.ends_at' => ['nullable', 'date_format:H:i'],
        ]);

        $activeEntries = $this->normalizeEntries($data['entries']);
        $this->ensureNoScheduleConflicts($activeEntries, $timetable);
        $this->syncEntries($timetable, $activeEntries);
        $this->generateSessions->handle($timetable->fresh(['group', 'entries']), now(), 7);

        return redirect()->route('admin.timetables.show', $timetable)->with('status', 'Timetable updated.');
    }

    public function destroy(Timetable $timetable): RedirectResponse
    {
        $this->deleteFuturePendingGeneratedSessionsForEntryIds($timetable->entries()->pluck('id')->all());
        $timetable->delete();

        return redirect()->route('admin.timetables.index')->with('status', 'Timetable deleted.');
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'group_id' => (string) $request->query('group_id', ''),
        ];
    }

    private function filteredIndexQuery(array $filters): Builder
    {
        return Timetable::query()
            ->with(['group', 'entries'])
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->whereHas('group', function ($query) use ($filters): void {
                    $query
                        ->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('subject', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['group_id'] !== '', function ($query) use ($filters): void {
                $query->where('teaching_group_id', $filters['group_id']);
            });
    }

    private function weekdayOptions(): array
    {
        return [
            ['value' => 'monday', 'label' => 'Monday'],
            ['value' => 'tuesday', 'label' => 'Tuesday'],
            ['value' => 'wednesday', 'label' => 'Wednesday'],
            ['value' => 'thursday', 'label' => 'Thursday'],
            ['value' => 'friday', 'label' => 'Friday'],
            ['value' => 'saturday', 'label' => 'Saturday'],
            ['value' => 'sunday', 'label' => 'Sunday'],
        ];
    }

    private function labelForDay(string $day): string
    {
        return collect($this->weekdayOptions())->firstWhere('value', $day)['label'] ?? ucfirst($day);
    }

    private function formatTime(?string $value): string
    {
        return $value ? substr($value, 0, 5) : '-';
    }

    private function entriesForForm(Timetable $timetable): array
    {
        return collect($this->weekdayOptions())->map(function (array $day) use ($timetable): array {
            $entry = $timetable->entries->firstWhere('day_of_week', $day['value']);

            return [
                'day' => $day['value'],
                'label' => $day['label'],
                'active' => (bool) $entry,
                'starts_at' => $entry ? $this->formatTime($entry->starts_at) : '',
                'ends_at' => $entry ? $this->formatTime($entry->ends_at) : '',
            ];
        })->all();
    }

    private function normalizeEntries(array $entries): array
    {
        $activeEntries = collect($entries)
            ->filter(fn (array $entry): bool => (bool) ($entry['active'] ?? false))
            ->map(function (array $entry): array {
                $startsAt = $entry['starts_at'] ?? null;
                $endsAt = $entry['ends_at'] ?? null;

                if (! $startsAt || ! $endsAt) {
                    throw ValidationException::withMessages([
                        'entries' => 'Each active timetable day requires a start and end time.',
                    ]);
                }

                if ($endsAt <= $startsAt) {
                    throw ValidationException::withMessages([
                        'entries' => 'Each active timetable day must end after it starts.',
                    ]);
                }

                return [
                    'day_of_week' => $entry['day'],
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ];
            })
            ->values();

        if ($activeEntries->isEmpty()) {
            throw ValidationException::withMessages([
                'entries' => 'Select at least one active timetable day.',
            ]);
        }

        return $activeEntries->all();
    }

    private function ensureNoScheduleConflicts(array $entries, ?Timetable $ignoredTimetable = null): void
    {
        foreach ($entries as $entry) {
            $conflictingEntry = TimetableEntry::query()
                ->with('timetable.group:id,name')
                ->where('day_of_week', $entry['day_of_week'])
                ->when($ignoredTimetable, fn ($query) => $query->whereHas('timetable', fn ($timetableQuery) => $timetableQuery->whereKeyNot($ignoredTimetable->getKey())))
                ->where('starts_at', '<', $entry['ends_at'])
                ->where('ends_at', '>', $entry['starts_at'])
                ->first();

            if (! $conflictingEntry) {
                continue;
            }

            $groupName = $conflictingEntry->timetable?->group?->name ?? 'another group';

            throw ValidationException::withMessages([
                'entries' => sprintf(
                    '%s %s - %s is already reserved for %s.',
                    $this->labelForDay($entry['day_of_week']),
                    $this->formatTime($entry['starts_at']),
                    $this->formatTime($entry['ends_at']),
                    $groupName
                ),
            ]);
        }
    }

    private function syncEntries(Timetable $timetable, array $activeEntries): void
    {
        $existingEntries = $timetable->entries()->get()->keyBy('day_of_week');
        $activeDays = collect($activeEntries)->pluck('day_of_week')->all();

        foreach ($activeEntries as $entry) {
            $storedEntry = $existingEntries->get($entry['day_of_week']);

            if ($storedEntry) {
                $storedEntry->update([
                    'starts_at' => $entry['starts_at'],
                    'ends_at' => $entry['ends_at'],
                ]);

                continue;
            }

            $timetable->entries()->create($entry);
        }

        $obsoleteEntryIds = $existingEntries
            ->reject(fn (TimetableEntry $entry): bool => in_array($entry->day_of_week, $activeDays, true))
            ->pluck('id')
            ->all();

        $this->deleteFuturePendingGeneratedSessionsForEntryIds($obsoleteEntryIds);

        if ($obsoleteEntryIds !== []) {
            $timetable->entries()->whereKey($obsoleteEntryIds)->delete();
        }
    }

    private function deleteFuturePendingGeneratedSessionsForEntryIds(array $entryIds): void
    {
        if ($entryIds === []) {
            return;
        }

        GroupSession::query()
            ->where('source_type', 'timetable')
            ->whereIn('timetable_entry_id', $entryIds)
            ->where('starts_at', '>=', now())
            ->doesntHave('attendanceRecords')
            ->delete();
    }
}
