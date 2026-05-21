<x-layouts.app title="Students">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Students</h1>
        <a href="{{ route('admin.students.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Student</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        @foreach($students as $student)
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 last:border-0">
                <div>
                    <div class="font-medium">{{ $student->name }}</div>
                    <div class="text-sm text-slate-600">Parent: {{ $student->parent->name }}</div>
                </div>
                <div class="text-sm text-slate-500">{{ $student->code }}</div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
