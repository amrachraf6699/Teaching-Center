<x-layouts.app title="Settings">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold">Settings</h1>
        <p class="mt-1 text-sm text-slate-600">Manage Teachify branding, contact details, social links, and report defaults.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            Please review the highlighted fields.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Brand</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium">Name</span>
                    <input name="name" value="{{ old('name', $settings->name) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                    @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium">Tagline</span>
                    <input name="tagline" value="{{ old('tagline', $settings->tagline) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </label>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium">Logo</span>
                    @if($media->getFirstMediaUrl('logo'))
                        <img src="{{ $media->getFirstMediaUrl('logo') }}" alt="Current logo" class="mt-2 h-14 w-auto rounded border border-slate-200 bg-white p-1">
                    @endif
                    <input name="logo" type="file" accept="image/*" class="mt-2 block w-full text-sm">
                    @error('logo') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="text-sm font-medium">Favicon</span>
                    @if($media->getFirstMediaUrl('favicon'))
                        <img src="{{ $media->getFirstMediaUrl('favicon') }}" alt="Current favicon" class="mt-2 h-10 w-10 rounded border border-slate-200 bg-white p-1">
                    @endif
                    <input name="favicon" type="file" accept="image/*" class="mt-2 block w-full text-sm">
                    @error('favicon') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Contact</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach([
                    'contact_email' => 'Email',
                    'contact_phone' => 'Phone',
                    'address' => 'Address',
                    'city' => 'City',
                    'country' => 'Country',
                ] as $field => $label)
                    <label class="block">
                        <span class="text-sm font-medium">{{ $label }}</span>
                        <input name="{{ $field }}" value="{{ old($field, $settings->{$field}) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @error($field) <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Social Links</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach([
                    'facebook_url' => 'Facebook',
                    'instagram_url' => 'Instagram',
                    'linkedin_url' => 'LinkedIn',
                    'youtube_url' => 'YouTube',
                    'website_url' => 'Website',
                ] as $field => $label)
                    <label class="block">
                        <span class="text-sm font-medium">{{ $label }}</span>
                        <input name="{{ $field }}" value="{{ old($field, $settings->{$field}) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @error($field) <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
                    </label>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Localization and Reports</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium">Timezone</span>
                    <select name="timezone" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                        @foreach($timezones as $timezone)
                            <option value="{{ $timezone }}" @selected(old('timezone', $settings->timezone) === $timezone)>{{ $timezone }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm font-medium">Locale</span>
                    <input name="locale" value="{{ old('locale', $settings->locale) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">
                </label>
            </div>

            <label class="mt-4 block">
                <span class="text-sm font-medium">Report Footer Text</span>
                <textarea name="report_footer_text" rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('report_footer_text', $settings->report_footer_text) }}</textarea>
            </label>

            <label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
                <input name="show_logo_on_reports" type="checkbox" value="1" @checked(old('show_logo_on_reports', $settings->show_logo_on_reports)) class="rounded border-slate-300">
                Show logo on reports
            </label>
        </section>

        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
            Save Settings
        </button>
    </form>
</x-layouts.app>
