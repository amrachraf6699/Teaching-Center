<x-layouts.app title="Add Student">
    <h1 class="mb-6 text-2xl font-semibold">Add Student</h1>
    <form method="POST" action="{{ route('admin.students.store') }}" class="max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Parent</span>
            <select name="parent_id" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="text-sm font-medium">Name</span>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Student Code</span>
            <input name="code" value="{{ old('code') }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Create Student</button>
    </form>
</x-layouts.app>
