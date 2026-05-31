<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(['en', 'ar'])],
        ]);

        $request->session()->put('locale', $validated['locale']);
        app()->setLocale($validated['locale']);

        return back();
    }
}
