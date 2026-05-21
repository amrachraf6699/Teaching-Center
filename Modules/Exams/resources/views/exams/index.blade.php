<x-layouts.app title="Exams">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Exams</h1>
        <a href="{{ route('admin.exams.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Exam</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        @foreach($exams as $exam)
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 last:border-0">
                <div>
                    <div class="font-medium">{{ $exam->title }}</div>
                    <div class="text-sm text-slate-600">{{ $exam->group->name }} · {{ $exam->exam_date->format('M j, Y') }}</div>
                </div>
                <div class="text-sm text-slate-500">{{ $exam->max_score }} points</div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
