<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    Mail::raw('Test email from MaRoom', function ($message) {
        $message->to('contact.lunartimes@gmail.com')
                ->subject('Test Email - MaRoom');
    });
    echo "Email sent successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Check your SMTP settings in .env\n";
}