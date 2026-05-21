<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Core\Models\SettingMedia;
use Modules\Core\Settings\GeneralSettings;

class SettingsController extends Controller
{
    public function edit(GeneralSettings $settings): View
    {
        $media = SettingMedia::brand();

        return view('core::settings.edit', [
            'settings' => $settings,
            'media' => $media,
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request, GeneralSettings $settings): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'locale' => ['required', 'string', 'max:12'],
            'report_footer_text' => ['nullable', 'string', 'max:500'],
            'show_logo_on_reports' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:2048'],
        ]);

        foreach (array_keys($this->settingsFields()) as $field) {
            $settings->{$field} = $data[$field] ?? null;
        }

        $settings->show_logo_on_reports = $request->boolean('show_logo_on_reports');
        $settings->save();

        $media = SettingMedia::brand();

        if ($request->hasFile('logo')) {
            $media->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        if ($request->hasFile('favicon')) {
            $media->addMediaFromRequest('favicon')->toMediaCollection('favicon');
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated.');
    }

    /**
     * @return array<string, true>
     */
    private function settingsFields(): array
    {
        return [
            'name' => true,
            'tagline' => true,
            'contact_email' => true,
            'contact_phone' => true,
            'address' => true,
            'city' => true,
            'country' => true,
            'facebook_url' => true,
            'instagram_url' => true,
            'linkedin_url' => true,
            'youtube_url' => true,
            'website_url' => true,
            'timezone' => true,
            'locale' => true,
            'report_footer_text' => true,
        ];
    }
}
