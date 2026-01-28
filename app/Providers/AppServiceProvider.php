<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Configure SMTP DSN with disabled certificate verification for local development
        if (config('app.env') === 'local' && !env('MAILER_DSN')) {
            $host = env('MAIL_HOST', 'smtp.mailgun.org');
            $port = env('MAIL_PORT', 587);
            $username = env('MAIL_USERNAME');
            $password = env('MAIL_PASSWORD');
            $encryption = env('MAIL_ENCRYPTION', 'tls');
            
            // Build DSN with verify_peer=false for local development
            $dsn = 'smtp://';
            if ($username && $password) {
                $dsn .= urlencode($username) . ':' . urlencode($password) . '@';
            }
            $dsn .= $host . ':' . $port;
            
            $params = [];
            if ($encryption) {
                $params[] = 'encryption=' . $encryption;
            }
            $params[] = 'verify_peer=false';
            $params[] = 'verify_peer_name=false';
            
            $dsn .= '?' . implode('&', $params);
            
            // Set MAILER_DSN environment variable
            putenv("MAILER_DSN={$dsn}");
            $_ENV['MAILER_DSN'] = $dsn;
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
    }
}
