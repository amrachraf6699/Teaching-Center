<?php

namespace Modules\People\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TableExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ParentAccountController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('Admin/Parents/Index', [
            'parents' => $this->filteredIndexQuery($filters)
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (User $parent): array => [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'email' => $parent->email,
                    'children_count' => $parent->children_count,
                    'show_url' => route('admin.parents.show', $parent),
                    'edit_url' => route('admin.parents.edit', $parent),
                    'delete_url' => route('admin.parents.destroy', $parent),
                    'created_at' => $parent->created_at?->toFormattedDateString(),
                ]),
            'filters' => $filters,
            'indexUrl' => route('admin.parents.index'),
            'createUrl' => route('admin.parents.create'),
            'exportUrls' => [
                'csv' => route('admin.parents.export', 'csv'),
                'pdf' => route('admin.parents.export', 'pdf'),
            ],
        ]);
    }

    public function export(Request $request, string $format): SymfonyResponse
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $rows = $this->filteredIndexQuery($this->filters($request))
            ->latest()
            ->get()
            ->map(fn (User $parent): array => [
                $parent->name,
                $parent->email,
                (string) $parent->children_count,
                $parent->created_at?->toFormattedDateString() ?? '-',
            ])
            ->all();

        $headers = ['Name', 'Email', 'Children', 'Created'];
        $filename = 'parents-export-'.now()->format('Ymd_His').'.'.$format;

        if ($format === 'csv') {
            return TableExport::csv($filename, $headers, $rows);
        }

        return TableExport::pdf($filename, 'Parents Export', $headers, $rows);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Parents/Create', [
            'action' => route('admin.parents.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'parent',
        ]);

        return redirect()->route('admin.parents.index')->with('status', 'Parent account created.');
    }

    public function show(User $parent): Response
    {
        $this->ensureParent($parent);

        $parent->load([
            'children.groups.sessions.attendanceRecords',
            'children.examResults.exam.group',
            'children.notifications',
            'parentNotifications.student',
        ])->loadCount('children');

        return Inertia::render('Admin/Parents/Show', [
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'email' => $parent->email,
                'children_count' => $parent->children_count,
                'created_at' => $parent->created_at?->toDayDateTimeString(),
                'edit_url' => route('admin.parents.edit', $parent),
                'index_url' => route('admin.parents.index'),
                'children' => $parent->children->map(fn ($student): array => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'code' => $student->code,
                    'phone' => $student->phone,
                    'show_url' => route('admin.students.show', $student),
                    'groups' => $student->groups->map(fn ($group): array => [
                        'id' => $group->id,
                        'name' => $group->name,
                        'subject' => $group->subject,
                        'sessions_count' => $group->sessions->count(),
                    ]),
                    'exam_results' => $student->examResults->map(fn ($result): array => [
                        'id' => $result->id,
                        'title' => $result->exam?->title,
                        'score' => $result->score,
                        'max_score' => $result->exam?->max_score,
                        'group' => $result->exam?->group?->name,
                        'schedule' => $result->exam?->start_at && $result->exam?->end_at
                            ? $result->exam->start_at->format('M j, Y g:i A').' - '.$result->exam->end_at->format('g:i A')
                            : '-',
                    ]),
                    'notifications' => $student->notifications->map(fn ($notification): array => [
                        'id' => $notification->id,
                        'recipient_role' => $notification->recipient_role,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'created_at' => $notification->created_at?->toDayDateTimeString(),
                    ]),
                ]),
                'notifications' => $parent->parentNotifications->map(fn ($notification): array => [
                    'id' => $notification->id,
                    'recipient_role' => $notification->recipient_role,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'student' => $notification->student?->name,
                    'created_at' => $notification->created_at?->toDayDateTimeString(),
                ]),
            ],
        ]);
    }

    public function edit(User $parent): Response
    {
        $this->ensureParent($parent);

        return Inertia::render('Admin/Parents/Edit', [
            'parent' => [
                'id' => $parent->id,
                'name' => $parent->name,
                'email' => $parent->email,
            ],
            'action' => route('admin.parents.update', $parent),
            'showUrl' => route('admin.parents.show', $parent),
        ]);
    }

    public function update(Request $request, User $parent): RedirectResponse
    {
        $this->ensureParent($parent);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($parent->id)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $parent->update([
            'name' => $data['name'],
            'email' => $data['email'],
            ...(filled($data['password'] ?? null) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        return redirect()->route('admin.parents.show', $parent)->with('status', 'Parent account updated.');
    }

    public function destroy(User $parent): RedirectResponse
    {
        $this->ensureParent($parent);
        $parent->delete();

        return redirect()->route('admin.parents.index')->with('status', 'Parent account deleted.');
    }

    private function ensureParent(User $parent): void
    {
        abort_unless($parent->isParent(), 404);
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'children' => (string) $request->query('children', ''),
        ];
    }

    private function filteredIndexQuery(array $filters): Builder
    {
        return User::query()
            ->where('role', 'parent')
            ->withCount('children')
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $query
                        ->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%");
                });
            })
            ->when($filters['children'] === 'with', function ($query): void {
                $query->has('children');
            })
            ->when($filters['children'] === 'without', function ($query): void {
                $query->doesntHave('children');
            });
    }
}
