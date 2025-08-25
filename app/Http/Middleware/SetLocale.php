<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'nl'];

        // 1) Authenticated user's preference
        $userLocale = optional($request->user())->preferred_locale;

        // 2) Session preference
        $sessionLocale = session('locale');

        // 3) Browser preference
        $browserLocale = $this->detectFromBrowser($request, $supported);

        $locale = $userLocale
            ?: ($sessionLocale ?: $browserLocale);

        if (!in_array($locale, $supported, true)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }

    private function detectFromBrowser(Request $request, array $supported): string
    {
        $preferred = $request->getPreferredLanguage($supported);
        if ($preferred) {
            return $preferred;
        }

        // Fallback: map generic language to supported if possible
        $accept = $request->header('Accept-Language');
        if (is_string($accept)) {
            $parts = explode(',', $accept);
            foreach ($parts as $part) {
                $code = strtolower(trim(explode(';', $part)[0]));
                $base = substr($code, 0, 2);
                if (in_array($base, $supported, true)) {
                    return $base;
                }
            }
        }

        return 'en';
    }
}
