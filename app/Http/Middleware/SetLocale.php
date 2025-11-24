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
        // Get locale from session, or fall back to config, or default to 'en'
        $locale = $request->session()->get('locale', config('app.locale', 'en'));
        
        // Set the application locale
        app()->setLocale($locale);
        
        return $next($request);
    }
}
