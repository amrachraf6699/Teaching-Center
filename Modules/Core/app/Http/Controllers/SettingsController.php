<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Core\Models\SettingMedia;
use Modules\Core\Settings\GeneralSettings;

class SettingsController extends Controller
{
    public function edit(GeneralSettings $settings): Response
    {
        $media = SettingMedia::brand();

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => $this->settingsPayload($settings),
            'media' => [
                'logoUrl' => $media->getFirstMediaUrl('logo') ?: null,
                'faviconUrl' => $media->getFirstMediaUrl('favicon') ?: null,
            ],
            'timezones' => timezone_identifiers_list(),
            'action' => route('admin.settings.update'),
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

        return redirect()->route('admin.settings.edit')->with('status', __('flash.settings.updated'));
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

    /**
     * @return array<string, mixed>
     */
    private function settingsPayload(GeneralSettings $settings): array
    {
        return [
            'name' => $settings->name,
            'tagline' => $settings->tagline,
            'contact_email' => $settings->contact_email,
            'contact_phone' => $settings->contact_phone,
            'address' => $settings->address,
            'city' => $settings->city,
            'country' => $settings->country,
            'facebook_url' => $settings->facebook_url,
            'instagram_url' => $settings->instagram_url,
            'linkedin_url' => $settings->linkedin_url,
            'youtube_url' => $settings->youtube_url,
            'website_url' => $settings->website_url,
            'timezone' => $settings->timezone,
            'locale' => $settings->locale,
            'report_footer_text' => $settings->report_footer_text,
            'show_logo_on_reports' => $settings->show_logo_on_reports,
        ];
    }
}
