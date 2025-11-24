<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log the CSRF token mismatch for debugging
            \Log::warning('CSRF token mismatch', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => $request->session()->getId(),
                'has_session' => $request->hasSession(),
                'token_from_request' => $request->input('_token'),
                'token_from_session' => $request->session()->token(),
                'cookies' => $request->cookies->all(),
            ]);

            // Regenerate token for next attempt
            $request->session()->regenerateToken();

            // For ngrok, try to regenerate session and redirect back
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch. Please refresh the page and try again.'], 419);
            }

            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->withErrors(['email' => 'Your session has expired. Please try again.']);
        }
    }
}
