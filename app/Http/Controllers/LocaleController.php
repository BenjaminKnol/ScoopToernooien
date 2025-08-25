<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'in:en,nl'],
            'redirect' => ['nullable', 'string'],
        ]);

        $locale = $validated['locale'];

        // Store in session for immediate effect
        session(['locale' => $locale]);

        // Persist to user profile if logged in
        if (Auth::check()) {
            $user = Auth::user();
            $user->preferred_locale = $locale;
            $user->save();
        }

        $to = $validated['redirect'] ?? url()->previous();

        return Redirect::to($to);
    }
}
