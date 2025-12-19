# Admin Role Setup Guide

## ✅ Admin Role and User Created Successfully!

The admin role has been created and assigned to a user.

---

## 📋 Usage Options

### Option 1: Create New Admin User (Interactive)
```bash
php artisan admin:create
```
This will prompt you for:
- Email address
- Name
- Password

### Option 2: Create Admin User with Options
```bash
php artisan admin:create --email=admin@example.com --name="Admin User" --password=password
```

### Option 3: Assign Admin Role to Existing User
```bash
php artisan admin:create --assign-to=user@example.com
```

---

## ✅ What Was Created

1. **Admin Role**: `admin` role created in the database
2. **Admin User**: User with email `admin@example.com`
   - Name: Admin User
   - Password: password
   - Role: admin

---

## 🔐 Login Credentials

**Default Admin User:**
- **Email:** admin@example.com
- **Password:** password

⚠️ **Important:** Change the password after first login!

---

## 🚀 Quick Start

1. **Login** at `/login` with the admin credentials
2. **Access Admin Panel** at `/admin/bookings`
3. **Change Password** in the profile settings

---

## 📝 Using the Seeder

You can also use the seeder:

```bash
php artisan db:seed --class=AdminUserSeeder
```

This creates:
- Email: admin@maroom.local
- Password: password

---

## 🔧 Assign Admin Role to Existing User

If you have an existing user and want to make them admin:

```bash
php artisan admin:create --assign-to=existing@user.com
```

Or manually via Tinker:
```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'your@email.com')->first();
$user->assignRole('admin');
```

---

## ✅ Verification

To verify admin users:
```bash
php artisan tinker
```

```php
// Check if admin role exists
\Spatie\Permission\Models\Role::where('name', 'admin')->exists();

// List all admin users
\App\Models\User::role('admin')->get(['name', 'email']);
```

---

## 🎯 Next Steps

1. ✅ Admin role created
2. ✅ Admin user created
3. ✅ Role assigned to user
4. 🔐 **Login and change password**
5. 🚀 **Start using the admin panel!**

---

**All set! You can now access the admin panel with the admin credentials.** 🎉

