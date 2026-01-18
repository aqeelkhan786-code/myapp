<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Ensure registered users do NOT have admin role - they are customers only
        // No role assignment needed - customers are identified by not having 'admin' role
        // Admin role is only assigned manually via admin:create command or seeder

        event(new Registered($user));

        Auth::login($user);

        // Check if there's an intended URL (e.g., from billing page redirect)
        $redirectUrl = $request->session()->pull('url.intended', null);
        
        // If no intended URL, redirect based on user role
        if (!$redirectUrl) {
            $redirectUrl = $user->hasRole('admin') ? \App\Providers\RouteServiceProvider::HOME : route('my-bookings');
        }
        
        return redirect($redirectUrl);
    }
}
