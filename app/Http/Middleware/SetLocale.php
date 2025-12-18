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
        // Get the configured default locale (should be 'de' for German)
        $defaultLocale = config('app.locale', 'de');
        
        // German is the primary language for this project
        // Always use the config default on first load, ignoring any stale session
        $sessionLocale = $request->session()->get('locale');
        
        // If config default is German, use it as the primary default
        if ($defaultLocale === 'de') {
            // Check if user has explicitly set a preference (tracked via a flag)
            // If no explicit preference, use German
            $hasExplicitPreference = $request->session()->get('locale_user_set', false);
            
            if ($hasExplicitPreference && $sessionLocale && in_array($sessionLocale, ['de', 'en'])) {
                // User has explicitly chosen a language, respect it
                $locale = $sessionLocale;
            } else {
                // No explicit preference or first visit - use German
                $locale = 'de';
                $request->session()->put('locale', 'de');
                // Don't mark as user-set, so it can be overridden by config
                $request->session()->put('locale_user_set', false);
            }
        } else {
            // Config is not German, use session or config default
            $locale = $sessionLocale ?: $defaultLocale;
            if (!in_array($locale, ['de', 'en'])) {
                $locale = $defaultLocale;
            }
            $request->session()->put('locale', $locale);
        }
        
        // Set the application locale
        app()->setLocale($locale);
        
        return $next($request);
    }
}
