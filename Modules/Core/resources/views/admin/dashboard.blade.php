<x-layouts.app title="Teacher Dashboard">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold">Teacher Dashboard</h1>
        <p class="mt-1 text-sm text-slate-600">Manage students, parents, groups, sessions, exams, grades, and notifications.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach([
            'Students' => $studentCount,
            'Parents' => $parentCount,
            'Groups' => $groupCount,
            'Sessions' => $sessionCount,
            'Exams' => $examCount,
        ] as $label => $value)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-slate-500">{{ $label }}</div>
                <div class="mt-2 text-3xl font-semibold">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('admin.parents.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Parent</a>
        <a href="{{ route('admin.students.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Student</a>
        <a href="{{ route('admin.groups.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Group</a>
        <a href="{{ route('admin.sessions.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Session</a>
        <a href="{{ route('admin.exams.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Exam</a>
    </div>
</x-layouts.app>
