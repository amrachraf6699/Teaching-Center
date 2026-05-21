<x-layouts.app title="Sessions">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Sessions</h1>
        <a href="{{ route('admin.sessions.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Session</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        @foreach($sessions as $session)
            <div class="border-b border-slate-100 px-4 py-3 last:border-0">
                <div class="font-medium">{{ $session->title }}</div>
                <div class="text-sm text-slate-600">{{ $session->group->name }} · {{ $session->starts_at->format('M j, Y g:i A') }}</div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
