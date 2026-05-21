<?php

namespace Modules\Core\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $name;

    public ?string $tagline;

    public ?string $contact_email;

    public ?string $contact_phone;

    public ?string $address;

    public ?string $city;

    public ?string $country;

    public ?string $facebook_url;

    public ?string $instagram_url;

    public ?string $linkedin_url;

    public ?string $youtube_url;

    public ?string $website_url;

    public string $timezone;

    public string $locale;

    public ?string $report_footer_text;

    public bool $show_logo_on_reports;

    public static function group(): string
    {
        return 'general';
    }
}
