<x-layouts.app :title="$title">
    <section class="mx-auto max-w-md">
        <h1 class="mb-2 text-2xl font-semibold">{{ $title }}</h1>
        <p class="mb-6 text-sm text-slate-600">
            Sign in with your teacher or parent account.
        </p>

        <form method="POST" action="{{ $action }}" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Email</span>
                <input name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                @error('email')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="block">
                <span class="text-sm font-medium text-slate-700">Password</span>
                <input name="password" type="password" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                @error('password')
                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input name="remember" type="checkbox" value="1" class="rounded border-slate-300">
                Remember me
            </label>

            <button class="w-full rounded-md bg-slate-950 px-4 py-2 font-medium text-white hover:bg-slate-800">
                Sign in
            </button>
        </form>
    </section>
</x-layouts.app>
