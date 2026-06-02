<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\People\Models\Student;

class AuthController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Auth/Login', [
            'title' => 'Login',
            'action' => route('login.store'),
            'quickLoginAccounts' => $this->quickLoginEnabled() ? $this->seededPortalAccounts() : [],
        ]);
    }

    public function studentLogin(Request $request): Response
    {
        return Inertia::render('Auth/StudentLogin', [
            'title' => 'Student Login',
            'action' => route('student.login.store'),
            'redirect' => (string) $request->query('redirect', ''),
            'quickLoginAccounts' => $this->quickLoginEnabled() ? $this->seededStudentAccounts() : [],
        ]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $role = $request->user()->role;

        return redirect()->intended($this->redirectPathForRole($role));
    }

    public function authenticateStudent(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'code' => ['required', 'string'],
            'password' => ['required', 'string'],
            'redirect' => ['nullable', 'string'],
        ]);

        $student = Student::query()
            ->with('user')
            ->where('code', $credentials['code'])
            ->first();

        if (! $student?->user || ! $student->user->isStudent() || ! Hash::check($credentials['password'], $student->user->password)) {
            throw ValidationException::withMessages([
                'code' => __('These credentials do not match our records.'),
            ]);
        }

        Auth::login($student->user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirect = (string) ($credentials['redirect'] ?? '');

        if ($redirect !== '' && str_starts_with($redirect, url('/'))) {
            return redirect()->to($redirect);
        }

        return redirect()->intended(route('student.home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectPathForRole(string $role): string
    {
        return match ($role) {
            'teacher' => route('admin.dashboard'),
            'student' => route('student.home'),
            default => route('parent.dashboard'),
        };
    }

    private function quickLoginEnabled(): bool
    {
        return ! app()->isProduction();
    }

    private function seededPortalAccounts(): array
    {
        return [
            [
                'role' => 'teacher',
                'name' => 'Teachify Teacher',
                'email' => 'teacher@teachify.test',
                'password' => 'password',
            ],
            [
                'role' => 'parent',
                'name' => 'Mona Hassan',
                'email' => 'mona.parent@teachify.test',
                'password' => 'password',
            ],
            [
                'role' => 'parent',
                'name' => 'Karim Saleh',
                'email' => 'karim.parent@teachify.test',
                'password' => 'password',
            ],
            [
                'role' => 'parent',
                'name' => 'Sara Nabil',
                'email' => 'sara.parent@teachify.test',
                'password' => 'password',
            ],
        ];
    }

    private function seededStudentAccounts(): array
    {
        return collect([
            ['name' => 'Omar Hassan', 'code' => 'ST-001'],
            ['name' => 'Laila Hassan', 'code' => 'ST-002'],
            ['name' => 'Youssef Saleh', 'code' => 'ST-003'],
            ['name' => 'Nour Saleh', 'code' => 'ST-004'],
            ['name' => 'Adam Nabil', 'code' => 'ST-005'],
            ['name' => 'Mariam Nabil', 'code' => 'ST-006'],
        ])->map(fn (array $student): array => [
            ...$student,
            'role' => 'student',
            'password' => $student['code'],
        ])->all();
    }
}
