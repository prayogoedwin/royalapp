# Laravel + Blade Starter Kit

---

## Introduction

Our Laravel 12 + Blade starter kit provides the typical functionality found in the Laravel Starter kits, but with a few key differences:

- A CoreUI/AdminLTE inspired design layout
- Blade + AlpineJS code

This kit aims to fill the gap where there is no simple **Blade only** starter kit available.

Our internal goal at Laravel Daily is to start using this starter kit for our Demo applications, to avoid overwhelming our audience with Vue/Livewire/React if we had used one of the official Laravel 12 starter kits.

**Note:** This is Work in Progress kit, so it will get updates and fixes/features as we go.

---

## Screenshots

### Authentication & Dashboard

![](https://laraveldaily.com/uploads/2025/05/LoginPage.png)

![](https://laraveldaily.com/uploads/2025/05/RegisterPage.png)

![](https://laraveldaily.com/uploads/2025/05/DashboardPage.png)

![](https://laraveldaily.com/uploads/2025/05/ProfilePage.png)

### RBAC - User Management

![Users List with DataTables](screenshots/users-list.png)
*User management with role badges, search, and pagination*

### RBAC - Role Management

![Roles List](screenshots/roles-list.png)
*Roles list with DataTables server-side processing*

![Edit Role with Grouped Permissions](screenshots/roles-edit.png)
*Edit role with permissions grouped by resource (Users, Roles, Permissions)*

### RBAC - Permission Management

![Permissions List](screenshots/permissions-list.png)
*Permission management with real-time search and export functionality*

---

## What is Inside?

Inside you will find all the functions that you would expect:

- Authentication
    - Login
    - Registration
    - Password Reset Flow
    - Email Confirmation Flow
- Dashboard Page
- Profile Settings
    - Profile Information Page
    - Password Update Page
    - Appearance Preferences
- **RBAC (Role-Based Access Control)**
    - User Management with Role Assignment
    - Role Management with Permissions
    - Permission Management
    - Middleware for Role & Permission Checks
- **Laravel Sanctum API**
    - Token-based Authentication
    - Sample Endpoints (Login, Users, Me, Logout)
    - Role & Permission Middleware for API
- **DataTables Integration**
    - Server-side Processing
    - Real-time Search & Pagination
    - Export to Excel/CSV
    - Row Striping & Sorting
- **Modern UI Features**
    - Dynamic Favicon
    - Footer with App Info & Version
    - Dark Mode Support
    - Responsive Design

---

## How to use it?

To use this kit, you can install it using:

```bash
laravel new --using=laraveldaily/starter-kit
```

Or clone this repository:

```bash
git clone https://github.com/yourusername/laravel-starter-kit.git
cd laravel-starter-kit
composer install
cp .env.example .env
php artisan key:generate
```

### Database Setup

```bash
# Configure your database in .env file, then:
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force
```

### Start Development Server

```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` and login with one of the demo accounts.

### Features Configuration

This starter kit includes:

- **No NPM/Vite required** - All frontend assets loaded via CDN (Tailwind CSS, Alpine.js, jQuery, DataTables)
- **Perfect for shared hosting** - See `DEPLOYMENT_SHARED_HOSTING.md` for deployment guide
- **Excel Export** - Download users, roles, and permissions as Excel/CSV
- **API Ready** - Laravel Sanctum configured with sample endpoints

From there, you can modify the kit to your needs.

---

## Documentation

For detailed information about RBAC and API features, please refer to:

- **[SCREENSHOTS.md](SCREENSHOTS.md)** - Detailed screenshots guide with feature explanations
- **[RBAC_API_GUIDE.md](RBAC_API_GUIDE.md)** - Complete guide for RBAC and Sanctum API
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Technical implementation details
- **[UI_GUIDE.md](UI_GUIDE.md)** - User interface design guide
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** - Quick reference for common tasks
- **[DEPLOYMENT_SHARED_HOSTING.md](DEPLOYMENT_SHARED_HOSTING.md)** - Deployment guide for shared hosting

### Demo Users

After running the seeder, you can login with:

- **Super Admin:** superadmin@example.com / password
- **Admin:** admin@example.com / password
- **Editor:** editor@example.com / password (view-only)
- **User:** user@example.com / password (no permissions)

### API Testing

Import the included `postman_collection.json` to Postman or use the examples in `api-examples.js` for testing the API endpoints.

---

## Design Elements

If you want to see examples of what design elements we have, you can [visit the Wiki](<https://github.com/LaravelDaily/starter-kit/wiki/Design-Examples-(Raw-Files)>) and see the raw HTML files.

---

## Licence

Starter kit is open-sourced software licensed under the MIT license.
