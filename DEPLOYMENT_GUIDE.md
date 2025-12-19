# 🚀 Laravel Booking System - Deployment Guide

**Domain:** Aapka domain already configured hai  
**Server:** cPanel/Shared Hosting ya VPS

---

## 📋 Pre-Deployment Checklist

### Server Requirements
- ✅ PHP >= 8.0
- ✅ Composer installed
- ✅ MySQL/MariaDB database
- ✅ Apache/Nginx web server
- ✅ SSL Certificate (recommended)
- ✅ SSH access (recommended)

---

## 🔧 Step 1: Server Preparation

### 1.1 Check PHP Version
```bash
php -v
# Should be PHP 8.0 or higher
```

### 1.2 Check Required PHP Extensions
```bash
php -m | grep -E "pdo|mbstring|openssl|tokenizer|xml|ctype|json|bcmath|fileinfo|gd|curl"
```

**Required Extensions:**
- pdo_mysql
- mbstring
- openssl
- tokenizer
- xml
- ctype
- json
- bcmath
- fileinfo
- gd (for image processing)
- curl (for iCal sync)

---

## 📤 Step 2: Upload Files to Server

### Option A: Using FTP/SFTP (cPanel File Manager)

1. **Compress Project Locally:**
   ```bash
   # Windows PowerShell mein
   Compress-Archive -Path * -DestinationPath booking-system.zip
   ```
   
   **Important:** Ye files **EXCLUDE** karein:
   - `node_modules/`
   - `vendor/` (server pe install hoga)
   - `.git/`
   - `.env` (server pe banayenge)
   - `storage/logs/*`
   - `storage/framework/cache/*`
   - `storage/framework/sessions/*`
   - `storage/framework/views/*`

2. **Upload to Server:**
   - cPanel File Manager kholo
   - `public_html` folder mein jao
   - `booking-system.zip` upload karo
   - Extract karo

3. **File Structure:**
   ```
   public_html/
   ├── app/
   ├── bootstrap/
   ├── config/
   ├── database/
   ├── public/          ← Ye folder important hai!
   ├── resources/
   ├── routes/
   ├── storage/
   └── ...
   ```

### Option B: Using Git (Recommended)

```bash
# Server pe SSH karke
cd /home/username/public_html
git clone https://github.com/yourusername/laravel-booking-system.git .
# Ya agar private repo hai to credentials use karein
```

### Option C: Using rsync (VPS)

```bash
rsync -avz --exclude 'node_modules' --exclude 'vendor' --exclude '.git' \
  ./ user@yourdomain.com:/home/username/public_html/
```

---

## 🗄️ Step 3: Database Setup

### 3.1 Create Database (cPanel)

1. cPanel → MySQL Databases
2. **Create Database:** `username_booking`
3. **Create User:** `username_booking_user`
4. **Set Password:** Strong password
5. **Add User to Database:** Full privileges

### 3.2 Note Database Credentials
```
Database Name: username_booking
Database User: username_booking_user
Database Password: your_password
Database Host: localhost (usually)
```

---

## ⚙️ Step 4: Environment Configuration

### 4.1 Create .env File

Server pe SSH karke ya File Manager se:

```bash
cd /home/username/public_html
cp .env.example .env
# Ya manually .env file banao
```

### 4.2 Configure .env File

```env
APP_NAME="Booking System"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Europe/Berlin
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_booking
DB_USERNAME=username_booking_user
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Stripe Payment Configuration
STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_webhook_secret

# iCal Webhook Secret (for API security)
ICAL_WEBHOOK_SECRET=your_random_secret_key_here
```

### 4.3 Generate Application Key

```bash
php artisan key:generate
```

---

## 📦 Step 5: Install Dependencies

### 5.1 Install Composer Dependencies

```bash
cd /home/username/public_html
composer install --optimize-autoloader --no-dev
```

**Note:** Agar `composer` command nahi mil raha:
```bash
# Download composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Use composer
php composer.phar install --optimize-autoloader --no-dev
```

### 5.2 Install NPM Dependencies (Optional - agar frontend build karna hai)

```bash
npm install
npm run build
```

---

## 🗃️ Step 6: Database Migration

### 6.1 Run Migrations

```bash
php artisan migrate --force
```

### 6.2 Seed Database (Optional - agar seeders hain)

```bash
php artisan db:seed --force
```

**Important:** Admin user manually banana padega:
```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('your_secure_password'),
    'email_verified_at' => now(),
]);
$user->assignRole('admin');
exit
```

---

## 📁 Step 7: File Permissions

### 7.1 Set Storage Permissions

```bash
cd /home/username/public_html

# Storage folders create karein agar nahi hain
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p storage/logs

# Permissions set karein
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Owner set karein (apne username se replace karein)
chown -R username:username storage
chown -R username:username bootstrap/cache
```

**cPanel File Manager se:**
- `storage/` folder → Right click → Change Permissions → 775
- `bootstrap/cache/` folder → Right click → Change Permissions → 775

---

## 🔗 Step 8: Storage Link

### 8.1 Create Symbolic Link

```bash
php artisan storage:link
```

Agar symbolic link nahi ban sakta (shared hosting), to `public/storage` manually create karein ya route use karein (already configured hai `routes/web.php` mein).

---

## 🌐 Step 9: Web Server Configuration

### Option A: Apache (.htaccess) - Already Configured

`public/.htaccess` already hai. Bas ensure karein ke:

1. **Document Root:** `public_html/public` hona chahiye
   - Ya `public_html` se `public` folder ko point karein

2. **cPanel Configuration:**
   - cPanel → Domains → Your Domain
   - Document Root: `/home/username/public_html/public`

### Option B: Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /home/username/public_html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 🔄 Step 10: Queue Worker Setup

### 10.1 Queue Configuration

`.env` mein:
```env
QUEUE_CONNECTION=database
```

### 10.2 Create Queue Tables

```bash
php artisan queue:table
php artisan migrate
```

### 10.3 Setup Queue Worker (cPanel Cron Jobs)

cPanel → Cron Jobs → Add New Cron Job:

```bash
# Queue worker (every minute)
* * * * * cd /home/username/public_html && php artisan queue:work --tries=3 --timeout=90 >> /dev/null 2>&1
```

**Ya agar supervisor available hai (VPS):**

Create `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/username/public_html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=username
numprocs=2
redirect_stderr=true
stdout_logfile=/home/username/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

---

## ⏰ Step 11: Cron Job Setup

### 11.1 Laravel Scheduler

cPanel → Cron Jobs → Add New Cron Job:

```bash
# Laravel scheduler (every minute - required!)
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

**Ye cron job zaroori hai kyunki:**
- iCal sync hourly chalta hai (`app/Console/Kernel.php`)

---

## 🔒 Step 12: SSL Certificate

### 12.1 Enable SSL (cPanel)

1. cPanel → SSL/TLS Status
2. Domain select karein
3. "Run AutoSSL" click karein
4. Ya manually Let's Encrypt certificate install karein

### 12.2 Force HTTPS

`.env` mein:
```env
APP_URL=https://yourdomain.com
```

`app/Providers/AppServiceProvider.php` mein add karein:
```php
public function boot(): void
{
    if (config('app.env') === 'production') {
        \URL::forceScheme('https');
    }
}
```

---

## 🧪 Step 13: Testing

### 13.1 Test Website

1. Browser mein open karein: `https://yourdomain.com`
2. Home page check karein
3. Login page test karein
4. Admin panel check karein

### 13.2 Test Queue

```bash
# Queue test karein
php artisan queue:work --once
```

### 13.3 Test Scheduler

```bash
# Scheduler manually run karein
php artisan schedule:run
```

### 13.4 Check Logs

```bash
tail -f storage/logs/laravel.log
```

---

## 🎯 Step 14: Production Optimizations

### 14.1 Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 14.2 Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 14.3 Disable Debug Mode

`.env` mein:
```env
APP_DEBUG=false
APP_ENV=production
```

---

## 🔐 Step 15: Security Checklist

- ✅ `.env` file secure hai (permissions 600)
- ✅ `APP_DEBUG=false` production mein
- ✅ Strong database password
- ✅ SSL certificate installed
- ✅ Admin user strong password se
- ✅ `storage/` aur `bootstrap/cache/` permissions correct
- ✅ `.env` file git mein commit nahi hui

---

## 📧 Step 16: Email Configuration

### 16.1 SMTP Settings (.env)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="Booking System"
```

### 16.2 Test Email

```bash
php artisan tinker
```

```php
Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')->subject('Test');
});
```

---

## 🐛 Troubleshooting

### Problem: 500 Internal Server Error

**Solution:**
1. Check `storage/logs/laravel.log`
2. Check file permissions
3. Check `.env` file configuration
4. Check `APP_DEBUG=true` temporarily to see errors

### Problem: Queue Jobs Not Running

**Solution:**
1. Check cron job configured hai
2. Check queue tables migrated hain
3. Check `QUEUE_CONNECTION=database` in `.env`
4. Manually test: `php artisan queue:work`

### Problem: Storage Files Not Accessible

**Solution:**
1. Run `php artisan storage:link`
2. Check `public/storage` symlink exists
3. Check file permissions
4. Use fallback route (already configured)

### Problem: iCal Sync Not Working

**Solution:**
1. Check cron job running hai
2. Check `app/Console/Kernel.php` schedule
3. Check iCal feed URLs accessible hain
4. Check `storage/logs/laravel.log` for errors

### Problem: Permission Denied

**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

---

## 📝 Post-Deployment Checklist

- [ ] Website accessible hai
- [ ] SSL certificate working hai
- [ ] Admin login working hai
- [ ] Database connected hai
- [ ] Queue worker running hai
- [ ] Cron job configured hai
- [ ] Email sending working hai
- [ ] File uploads working hain
- [ ] Payment integration tested hai
- [ ] iCal sync tested hai
- [ ] Logs check kiye hain

---

## 🎉 Deployment Complete!

Aapka booking system ab live hai! 

**Important URLs:**
- Home: `https://yourdomain.com`
- Admin Panel: `https://yourdomain.com/admin/bookings`
- Login: `https://yourdomain.com/login`

**Next Steps:**
1. Admin user create karein
2. Properties/Rooms add karein
3. Stripe keys configure karein
4. Email settings test karein
5. iCal feeds configure karein

---

## 📞 Support

Agar koi problem aaye to:
1. Check `storage/logs/laravel.log`
2. Check server error logs
3. Verify all configurations
4. Test step by step

**Good Luck! 🚀**







