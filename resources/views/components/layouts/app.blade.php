<!DOCTYPE html>
@php
    $generalSettings = rescue(fn () => app(\Modules\Core\Settings\GeneralSettings::class), null, false);
    $brandMedia = rescue(fn () => \Modules\Core\Models\SettingMedia::brand(), null, false);
    $appName = $generalSettings?->name ?? 'Teachify';
    $logoUrl = $brandMedia?->getFirstMediaUrl('logo');
    $faviconUrl = $brandMedia?->getFirstMediaUrl('favicon');
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $appName }}</title>
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="min-h-screen bg-slate-50 text-slate-950 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2 text-lg font-semibold">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto">
                @else
                    <span>{{ $appName }}</span>
                @endif
            </a>
            <nav class="flex items-center gap-4 text-sm text-slate-600">
                @auth
                    @if(auth()->user()->isTeacher())
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-950">Dashboard</a>
                        <a href="{{ route('admin.students.index') }}" class="hover:text-slate-950">Students</a>
                        <a href="{{ route('admin.groups.index') }}" class="hover:text-slate-950">Groups</a>
                        <a href="{{ route('admin.exams.index') }}" class="hover:text-slate-950">Exams</a>
                        <a href="{{ route('admin.settings.edit') }}" class="hover:text-slate-950">Settings</a>
                    @else
                        <a href="{{ route('parent.dashboard') }}" class="hover:text-slate-950">Parent Portal</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-100">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-slate-950">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if(session('status'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
