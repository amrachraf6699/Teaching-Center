<x-layouts.app title="Add Parent">
    <h1 class="mb-6 text-2xl font-semibold">Add Parent</h1>
    <form method="POST" action="{{ route('admin.parents.store') }}" class="max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Name</span>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Email</span>
            <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm font-medium">Password</span>
            <input name="password" type="password" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
        </label>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white">Create Parent</button>
    </form>
</x-layouts.app>
