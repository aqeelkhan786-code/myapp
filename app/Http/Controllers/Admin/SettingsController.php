<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display settings page
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string|timezone',
            'locale' => 'required|string|in:en,de',
        ]);

        // Update .env or config cache
        $envPath = base_path('.env');
        $env = file_get_contents($envPath);
        
        if (preg_match('/^APP_TIMEZONE=.*/m', $env)) {
            $env = preg_replace('/^APP_TIMEZONE=.*/m', 'APP_TIMEZONE=' . $request->timezone, $env);
        } else {
            $env .= "\nAPP_TIMEZONE=" . $request->timezone;
        }
        
        // Update locale
        if (preg_match('/^APP_LOCALE=.*/m', $env)) {
            $env = preg_replace('/^APP_LOCALE=.*/m', 'APP_LOCALE=' . $request->locale, $env);
        } else {
            $env .= "\nAPP_LOCALE=" . $request->locale;
        }
        
        file_put_contents($envPath, $env);
        
        // Set locale in session for immediate effect
        app()->setLocale($request->locale);
        session(['locale' => $request->locale]);
        
        return back()->with('success', __('settings.settings_updated'));
    }

    /**
     * Update landlord information
     */
    public function updateLandlord(Request $request)
    {
        $request->validate([
            'landlord_name' => 'required|string|max:255',
            'landlord_address' => 'nullable|string|max:255',
            'landlord_postal_code' => 'nullable|string|max:20',
            'landlord_city' => 'nullable|string|max:100',
            'landlord_phone' => 'nullable|string|max:50',
            'landlord_email' => 'nullable|email|max:255',
        ]);

        $envPath = base_path('.env');
        $env = file_get_contents($envPath);
        
        $fields = [
            'LANDLORD_NAME' => $request->landlord_name,
            'LANDLORD_ADDRESS' => $request->landlord_address ?? '',
            'LANDLORD_POSTAL_CODE' => $request->landlord_postal_code ?? '',
            'LANDLORD_CITY' => $request->landlord_city ?? '',
            'LANDLORD_PHONE' => $request->landlord_phone ?? '',
            'LANDLORD_EMAIL' => $request->landlord_email ?? '',
        ];

        foreach ($fields as $key => $value) {
            if (preg_match('/^' . $key . '=.*/m', $env)) {
                $env = preg_replace('/^' . $key . '=.*/m', $key . '=' . $value, $env);
            } else {
                $env .= "\n" . $key . "=" . $value;
            }
        }
        
        file_put_contents($envPath, $env);
        
        // Clear config cache
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            unlink(base_path('bootstrap/cache/config.php'));
        }
        
        return back()->with('success', __('settings.landlord_settings_updated') ?? 'Landlord information updated successfully.');
    }

    /**
     * Update payment settings
     */
    public function updatePayment(Request $request)
    {
        $request->validate([
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
        ]);

        $envPath = base_path('.env');
        $env = file_get_contents($envPath);
        
        if ($request->stripe_key) {
            if (preg_match('/^STRIPE_KEY=.*/m', $env)) {
                $env = preg_replace('/^STRIPE_KEY=.*/m', 'STRIPE_KEY=' . $request->stripe_key, $env);
            } else {
                $env .= "\nSTRIPE_KEY=" . $request->stripe_key;
            }
        }
        
        if ($request->stripe_secret) {
            if (preg_match('/^STRIPE_SECRET=.*/m', $env)) {
                $env = preg_replace('/^STRIPE_SECRET=.*/m', 'STRIPE_SECRET=' . $request->stripe_secret, $env);
            } else {
                $env .= "\nSTRIPE_SECRET=" . $request->stripe_secret;
            }
        }
        
        file_put_contents($envPath, $env);
        
        return back()->with('success', __('settings.payment_settings_updated'));
    }

    /**
     * Update email template
     */
    public function updateEmailTemplate(Request $request)
    {
        $request->validate([
            'template' => 'required|string|in:booking_confirmation,document_sent',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Store email templates in storage or database
        $templatePath = storage_path('app/email-templates/' . $request->template . '.blade.php');
        $templateDir = dirname($templatePath);
        
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }
        
        file_put_contents($templatePath, $request->body);
        
        return back()->with('success', __('settings.email_template_updated'));
    }
}
