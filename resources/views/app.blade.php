@php
    $settings = rescue(fn () => app(\Modules\Core\Settings\GeneralSettings::class), null, false);
    $media = rescue(fn () => \Modules\Core\Models\SettingMedia::brand(), null, false);
    $appName = $settings?->name ?: config('app.name', 'Teachify');
    $faviconUrl = $media?->getFirstMediaUrl('favicon') ?: null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-app-name="{{ $appName }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ $appName }}</title>
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" sizes="any">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
