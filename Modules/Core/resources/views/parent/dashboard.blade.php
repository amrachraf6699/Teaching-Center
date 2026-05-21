<x-layouts.app title="Parent Portal">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold">Parent Portal</h1>
        <p class="mt-1 text-sm text-slate-600">Your children, sessions, attendance, grades, and updates.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <section class="space-y-4">
            @forelse($children as $child)
                <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ $child->name }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $child->groups->count() }} groups assigned</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($child->groups as $group)
                            <div class="rounded-md border border-slate-200 p-4">
                                <div class="font-medium">{{ $group->name }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $group->subject ?? 'General' }}</div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600">
                    No children are linked to this parent account yet.
                </div>
            @endforelse
        </section>

        <aside class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-semibold">Notifications</h2>
            <div class="mt-4 space-y-4">
                @forelse($notifications as $notification)
                    <div class="border-b border-slate-100 pb-3 last:border-0">
                        <div class="text-sm font-medium">{{ $notification->title }}</div>
                        <p class="mt-1 text-sm text-slate-600">{{ $notification->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-600">No notifications yet.</p>
                @endforelse
            </div>
        </aside>
    </div>
</x-layouts.app>
