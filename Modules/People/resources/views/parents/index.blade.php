<x-layouts.app title="Parents">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Parents</h1>
        <a href="{{ route('admin.parents.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Add Parent</a>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        @foreach($parents as $parent)
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 last:border-0">
                <div>
                    <div class="font-medium">{{ $parent->name }}</div>
                    <div class="text-sm text-slate-600">{{ $parent->email }}</div>
                </div>
                <div class="text-sm text-slate-500">{{ $parent->children_count }} children</div>
            </div>
        @endforeach
    </div>
</x-layouts.app>
