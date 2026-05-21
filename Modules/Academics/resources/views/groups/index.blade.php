<x-layouts.app title="Groups">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Groups</h1>
        <a href="{{ route('admin.groups.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Group</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        @foreach($groups as $group)
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 last:border-0">
                <div>
                    <div class="font-medium">{{ $group->name }}</div>
                    <div class="text-sm text-slate-600">{{ $group->subject ?? 'General' }}</div>
                </div>
                <div class="text-sm text-slate-500">{{ $group->students_count }} students</div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
