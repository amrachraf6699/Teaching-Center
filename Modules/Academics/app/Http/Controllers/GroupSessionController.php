<?php

namespace Modules\Academics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Academics\Models\GroupSession;
use Modules\Academics\Models\TeachingGroup;

class GroupSessionController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('Admin/Sessions/Index', [
            'sessions' => $this->filteredIndexQuery($filters)
                ->latest('starts_at')
                ->paginate(20)
                ->withQueryString()
                ->through(fn (GroupSession $session): array => [
                    'id' => $session->id,
                    'title' => $session->title,
                    'source_type' => $session->source_type,
                    'source_label' => $session->isGenerated() ? 'Generated' : 'Manual',
                    'starts_at' => $session->starts_at?->toDayDateTimeString(),
                    'ends_at' => $session->ends_at?->toDayDateTimeString(),
                    'group' => $session->group ? [
                        'id' => $session->group->id,
                        'name' => $session->group->name,
                        'subject' => $session->group->subject,
                    ] : null,
                    'show_url' => route('admin.sessions.show', $session),
                    'edit_url' => route('admin.sessions.edit', $session),
                    'delete_url' => route('admin.sessions.destroy', $session),
                ]),
            'filters' => $filters,
            'groupOptions' => TeachingGroup::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (TeachingGroup $group): array => [
                    'value' => (string) $group->id,
                    'label' => $group->name,
                ]),
            'indexUrl' => route('admin.sessions.index'),
            'createUrl' => route('admin.sessions.create'),
            'exportUrls' => [
                'csv' => route('admin.sessions.export', 'csv'),
                'pdf' => route('admin.sessions.export', 'pdf'),
            ],
            'importOptions' => [
                $this->importOption('sessions', 'Sessions'),
                $this->importOption('attendance', 'Attendance'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sessions/Create', [
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.sessions.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'attendance_entry_enabled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['source_type'] = 'manual';
        $data['session_date'] = substr((string) $data['starts_at'], 0, 10);
        $data['attendance_entry_enabled'] = $request->boolean('attendance_entry_enabled', true);

        $session = GroupSession::create($data);
        $session->ensureManualAttendanceCode();

        return redirect()->route('admin.sessions.index')->with('status', 'Session created.');
    }

    public function show(GroupSession $session): Response
    {
        $session->load(['group.students.parent', 'attendanceRecords.student.parent']);

        return Inertia::render('Admin/Sessions/Show', [
            'session' => [
                'id' => $session->id,
                'title' => $session->title,
                'source_type' => $session->source_type,
                'source_label' => $session->isGenerated() ? 'Generated from timetable' : 'Manual session',
                'scan_url' => URL::signedRoute('student.sessions.scan', ['session' => $session]),
                'attendance_entry_enabled' => $session->attendance_entry_enabled,
                'manual_attendance_code' => $session->ensureManualAttendanceCode(),
                'regenerate_attendance_code_url' => route('admin.sessions.regenerate-attendance-code', $session),
                'update_attendance_entry_url' => route('admin.sessions.update-attendance-entry', $session),
                'attendance_action' => route('admin.attendance.store'),
                'starts_at' => $session->starts_at?->toDayDateTimeString(),
                'ends_at' => $session->ends_at?->toDayDateTimeString(),
                'notes' => $session->notes,
                'edit_url' => route('admin.sessions.edit', $session),
                'index_url' => route('admin.sessions.index'),
                'group' => $session->group ? [
                    'id' => $session->group->id,
                    'name' => $session->group->name,
                    'subject' => $session->group->subject,
                    'show_url' => route('admin.groups.show', $session->group),
                ] : null,
                'students' => $session->group?->students->map(fn ($student): array => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'code' => $student->code,
                    'parent' => $student->parent?->name,
                    'attendance' => $session->attendanceRecords->where('student_id', $student->id)->first()?->status,
                    'attendance_notes' => $session->attendanceRecords->where('student_id', $student->id)->first()?->notes,
                    'show_url' => route('admin.students.show', $student),
                ])->values() ?? [],
                'attendance' => $session->attendanceRecords->map(fn ($attendance): array => [
                    'id' => $attendance->id,
                    'student' => $attendance->student?->name,
                    'parent' => $attendance->student?->parent?->name,
                    'status' => $attendance->status,
                    'notes' => $attendance->notes,
                ]),
            ],
        ]);
    }

    public function edit(GroupSession $session): Response
    {
        return Inertia::render('Admin/Sessions/Edit', [
            'session' => [
                'id' => $session->id,
                'teaching_group_id' => $session->teaching_group_id,
                'title' => $session->title,
                'starts_at' => $session->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $session->ends_at?->format('Y-m-d\TH:i'),
                'attendance_entry_enabled' => $session->attendance_entry_enabled,
                'notes' => $session->notes,
            ],
            'groups' => TeachingGroup::query()->orderBy('name')->get(['id', 'name', 'subject']),
            'action' => route('admin.sessions.update', $session),
            'showUrl' => route('admin.sessions.show', $session),
        ]);
    }

    public function update(Request $request, GroupSession $session): RedirectResponse
    {
        $data = $request->validate([
            'teaching_group_id' => ['required', 'exists:teaching_groups,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'attendance_entry_enabled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['source_type'] = 'manual';
        $data['session_date'] = substr((string) $data['starts_at'], 0, 10);
        $data['timetable_entry_id'] = null;
        $data['attendance_entry_enabled'] = $request->boolean('attendance_entry_enabled', true);
        $session->update($data);
        $session->ensureManualAttendanceCode();

        return redirect()->route('admin.sessions.show', $session)->with('status', 'Session updated.');
    }

    public function regenerateAttendanceCode(GroupSession $session): RedirectResponse
    {
        $session->update([
            'manual_attendance_code' => $session->generateManualAttendanceCode(),
        ]);

        return back()->with('status', 'Attendance code regenerated.');
    }

    public function updateAttendanceEntry(Request $request, GroupSession $session): RedirectResponse
    {
        $data = $request->validate([
            'attendance_entry_enabled' => ['required', 'boolean'],
        ]);

        $session->update([
            'attendance_entry_enabled' => $data['attendance_entry_enabled'],
        ]);

        return back()->with('status', 'Session self check-in updated.');
    }

    public function destroy(GroupSession $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')->with('status', 'Session deleted.');
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'group' => (string) $request->query('group', ''),
        ];
    }

    private function filteredIndexQuery(array $filters): Builder
    {
        return GroupSession::query()
            ->with('group')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query
                        ->where('title', 'like', "%{$filters['search']}%")
                        ->orWhere('notes', 'like', "%{$filters['search']}%")
                        ->orWhereHas('group', function ($query) use ($filters): void {
                            $query
                                ->where('name', 'like', "%{$filters['search']}%")
                                ->orWhere('subject', 'like', "%{$filters['search']}%");
                        });
                });
            })
            ->when($filters['group'] !== '', function ($query) use ($filters): void {
                $query->where('teaching_group_id', $filters['group']);
            });
    }

    private function importOption(string $type, string $label): array
    {
        return [
            'value' => $type,
            'label' => $label,
            'store_url' => route('admin.imports.store', $type),
            'template_csv_url' => route('admin.imports.template', ['type' => $type, 'format' => 'csv']),
            'template_xlsx_url' => route('admin.imports.template', ['type' => $type, 'format' => 'xlsx']),
        ];
    }
}
