<x-layouts.app title="Add Exam">
    <h1 class="mb-6 text-2xl font-semibold">Add Exam</h1>
    <form method="POST" action="{{ route('admin.exams.store') }}" class="max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
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
            <span class="text-sm font-medium">Exam Date</span>
            <input name="exam_date" type="date" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Max Score</span>
            <input name="max_score" type="number" min="1" step="0.01" value="100" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Create Exam</button>
    </form>
</x-layouts.app>
