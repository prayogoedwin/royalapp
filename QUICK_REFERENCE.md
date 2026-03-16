# Quick Reference - RBAC & Sanctum API

## 🔑 Demo Users
```
superadmin@example.com / password  (super-admin role - highest access)
admin@example.com      / password  (admin role - full access)
editor@example.com     / password  (editor role - view only)
user@example.com       / password  (user role - no permissions)
```

## 🌐 Web Routes
```
/users              - User management
/roles              - Role management
/permissions        - Permission management
/users/create       - Create new user
/roles/create       - Create new role
/permissions/create - Create new permission
```

## 🔌 API Endpoints

### Login
```bash
POST /api/login
Body: {"email": "...", "password": "..."}
→ Returns: {"token": "...", "user": {...}}
```

### Authenticated Routes (add header: Authorization: Bearer {token})
```bash
GET  /api/me                    - Get current user info
GET  /api/users                 - Get all users (admin only)
POST /api/logout                - Logout & revoke token
```

## 💻 Code Usage

### Check Role
```php
if ($user->hasRole('admin')) {
    // User is admin
}
```

### Check Permission
```php
if ($user->hasPermission('create-users')) {
    // User can create users
}
```

### Assign Role
```php
$user->assignRole('editor');
```

### Use Middleware
```php
// In routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin only routes
});

Route::middleware(['auth', 'permission:create-posts'])->group(function () {
    // Users with 'create-posts' permission
});
```

## 📦 Database Tables
- `users` - User accounts
- `roles` - Role definitions
- `permissions` - Permission definitions
- `role_user` - User-role relationships
- `permission_role` - Permission-role relationships
- `personal_access_tokens` - API tokens

## 🎯 Permissions Created by Seeder
```
view-users, create-users, edit-users, download-users, delete-users
view-roles, create-roles, edit-roles, download-roles, delete-roles
view-permissions, create-permissions, edit-permissions, download-permissions, delete-permissions
```

**Permission Order:**
1. View (lihat data)
2. Create (buat baru)
3. Edit (ubah)
4. Download (export/unduh)
5. Delete (hapus)

## 🔥 Quick Commands
```bash
# Run migrations
php artisan migrate --force

# Seed demo data
php artisan db:seed --class=RolePermissionSeeder --force

# Start dev server
php artisan serve

# Clear cache
php artisan config:clear
php artisan cache:clear
```

## 📱 JavaScript API Example
```javascript
// Login
const response = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'admin@example.com',
        password: 'password'
    })
});
const { token, user } = await response.json();

// Use token for authenticated requests
const usersResponse = await fetch('http://localhost:8000/api/users', {
    headers: { 'Authorization': `Bearer ${token}` }
});
const users = await usersResponse.json();
```

## 🚀 Testing with cURL
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get users (replace TOKEN)
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer TOKEN" \
  -H "Accept: application/json"
```

## 📚 Documentation Files
- `RBAC_API_GUIDE.md` - Complete documentation
- `IMPLEMENTATION_SUMMARY.md` - Implementation details
- `postman_collection.json` - Postman import file
- `api-examples.js` - JavaScript examples
- `QUICK_REFERENCE.md` - This file
