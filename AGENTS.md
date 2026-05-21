# Teachify Agent Instructions

## Project Shape
- Laravel 13, PHP 8.3, Pest, Blade, Tailwind CSS, and Vite.
- Business code belongs in `Modules/*` via `nwidart/laravel-modules`.
- Use coarse domain modules: `Core`, `People`, `Academics`, `Exams`, `Notifications`, and `ImportsExports`.
- Prefer module artisan generators for new module classes, then adjust generated code to local conventions.
- Composer module autoloading uses `extra.merge-plugin.include = ["Modules/*/composer.json"]`; run `composer dump-autoload` after adding modules/classes.

## Product Rules
- Teachify serves a private teacher with teacher/admin and parent portal surfaces.
- V1 is English LTR and server-rendered Blade + Tailwind with minimal JavaScript.
- No billing, fees, online payments, WhatsApp, SMS, Livewire, Inertia, React, or Vue unless explicitly requested later.
- Parent relationship is one parent account to many students; each student belongs to one parent.
- Parents must only access their own children, attendance, exam results, grades, and notifications.

## Implementation Rules
- Keep auth/session plumbing in the app layer when needed, but keep domain behavior inside modules.
- Use role-aware access: `teacher` for admin pages and `parent` for the parent portal.
- Persist parent notifications and keep delivery behind a service/interface so email, SMS, or WhatsApp can be added later.
- Activity logging should be used for important admin mutations once the audit tables/config are enabled.
- Media library is reserved for later student attachments/profile documents.
- Write labels and copy so they can be moved to localization files when multilingual support is requested.

## Verification
- Add focused Pest feature tests for auth separation and module behavior.
- Run `composer test` for backend changes and `npm run build` after Blade/Tailwind changes.
- Local PHP CLI may emit XAMPP extension/SNMP warnings; treat those as environment noise unless the Laravel command itself fails.
