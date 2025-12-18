# ⚡ Quick Deployment Commands

Yeh quick reference hai step-by-step commands ke liye.

## 🚀 Quick Steps (SSH Access Hai To)

```bash
# 1. Server pe connect karein
ssh username@yourdomain.com

# 2. Project folder mein jao
cd /home/username/public_html

# 3. Files upload karne ke baad, dependencies install karein
composer install --optimize-autoloader --no-dev

# 4. .env file configure karein (manually edit karein)
nano .env
# Ya
vi .env

# 5. Application key generate karein
php artisan key:generate

# 6. Database migrate karein
php artisan migrate --force

# 7. Storage link create karein
php artisan storage:link

# 8. Permissions set karein
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache

# 9. Queue tables create karein
php artisan queue:table
php artisan migrate

# 10. Cache optimize karein
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Test karein
php artisan queue:work --once
php artisan schedule:run
```

## 📋 cPanel File Manager Se (SSH Nahi Hai)

### Step 1: Files Upload
1. cPanel → File Manager
2. `public_html` folder mein jao
3. ZIP file upload karein
4. Extract karein

### Step 2: .env File
1. File Manager se `.env.example` copy karein
2. `.env` naam se rename karein
3. Edit karein aur database credentials add karein

### Step 3: Composer Install
1. cPanel → Terminal (ya SSH)
2. Commands run karein (upar wale)

### Step 4: Permissions
1. File Manager se:
   - `storage/` → Right click → Change Permissions → 775
   - `bootstrap/cache/` → Right click → Change Permissions → 775

### Step 5: Cron Jobs
1. cPanel → Cron Jobs
2. Add these two:

**Queue Worker:**
```
* * * * * cd /home/username/public_html && php artisan queue:work --tries=3 --timeout=90 >> /dev/null 2>&1
```

**Laravel Scheduler:**
```
* * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

## 🔧 Important .env Settings

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls

STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
```

## ✅ Post-Deployment Test

```bash
# 1. Website check karein
curl -I https://yourdomain.com

# 2. Queue test
php artisan queue:work --once

# 3. Logs check
tail -f storage/logs/laravel.log

# 4. Admin user create
php artisan tinker
```

Tinker mein:
```php
$user = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('SecurePassword123!'),
    'email_verified_at' => now(),
]);
$user->assignRole('admin');
exit
```

## 🐛 Common Issues

**500 Error:**
- Check `storage/logs/laravel.log`
- Check permissions
- Check `.env` file

**Queue Not Working:**
- Check cron job
- Check `QUEUE_CONNECTION=database`
- Run `php artisan queue:work` manually

**Storage Files:**
- Run `php artisan storage:link`
- Check permissions on `storage/` folder

---

**Detailed guide ke liye `DEPLOYMENT_GUIDE.md` dekhein!**






