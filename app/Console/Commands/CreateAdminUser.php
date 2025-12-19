<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--email= : Email address for the admin user}
                            {--name= : Name for the admin user}
                            {--password= : Password for the admin user}
                            {--assign-to= : Assign admin role to existing user by email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin role and assign it to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->info('✓ Admin role created/verified');

        // Check if assigning to existing user
        if ($email = $this->option('assign-to')) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("User with email '{$email}' not found!");
                return 1;
            }

            if ($user->hasRole('admin')) {
                $this->warn("User '{$email}' already has admin role.");
                return 0;
            }

            $user->assignRole('admin');
            $this->info("✓ Admin role assigned to user: {$email}");
            return 0;
        }

        // Create new admin user
        $email = $this->option('email') ?: $this->ask('Enter email address for admin user');
        $name = $this->option('name') ?: $this->ask('Enter name for admin user', 'Admin User');
        $password = $this->option('password') ?: $this->secret('Enter password for admin user');

        if (empty($password)) {
            $this->error('Password is required!');
            return 1;
        }

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            if ($user->hasRole('admin')) {
                $this->warn("User '{$email}' already exists and has admin role.");
                return 0;
            }

            // User exists but doesn't have admin role
            if ($this->confirm("User '{$email}' already exists. Assign admin role to this user?", true)) {
                $user->assignRole('admin');
                $this->info("✓ Admin role assigned to existing user: {$email}");
                return 0;
            }
        } else {
            // Create new user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            $this->info("✓ Admin user created: {$email}");
        }

        // Assign admin role
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        $this->info("✓ Admin role assigned to user: {$email}");
        $this->newLine();
        $this->info('Admin user credentials:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $email],
                ['Name', $user->name],
                ['Password', $password],
            ]
        );

        return 0;
    }
}

