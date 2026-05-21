<x-layouts.app title="Add Group">
    <h1 class="mb-6 text-2xl font-semibold">Add Group</h1>
    <form method="POST" action="{{ route('admin.groups.store') }}" class="max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Name</span>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Subject</span>
            <input name="subject" value="{{ old('subject') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Students</span>
            <select name="student_ids[]" multiple class="mt-1 h-40 w-full rounded-md border border-slate-300 px-3 py-2">
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </label>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Create Group</button>
    </form>
</x-layouts.app>
