<x-layouts.app title="Add Session">
    <h1 class="mb-6 text-2xl font-semibold">Add Session</h1>
    <form method="POST" action="{{ route('admin.sessions.store') }}" class="max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Group</span>
            <select name="teaching_group_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="text-sm font-medium">Title</span>
            <input name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Starts At</span>
            <input name="starts_at" type="datetime-local" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Create Session</button>
    </form>
</x-layouts.app>
