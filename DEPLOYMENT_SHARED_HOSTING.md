# Deployment ke Shared Hosting

## ✅ Sistem Diubah ke CDN

Sistem dimodifikasi untuk **tidak memerlukan npm/Vite**. Semua asset dimuat dari CDN:

### CDN yang Digunakan:
- **Tailwind CSS** - `cdn.tailwindcss.com`
- **Alpine.js** - `cdn.jsdelivr.net` 
- **Font Awesome** - `cdnjs.cloudflare.com`

## 📦 File yang Perlu Diupload

Upload semua file project **KECUALI**:
- `/node_modules` (tidak perlu)
- `/public/build` (tidak perlu)
- `/vendor` (akan di-generate)
- `/.env` (buat manual di hosting)

## 🚀 Langkah Deployment

### 1. Upload Files ke Hosting
Upload semua file project ke folder hosting (biasanya `public_html` atau `www`)

### 2. Struktur Folder di Hosting
```
your-domain.com/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← Document Root harus di sini!
│   ├── index.php
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
└── composer.json
```

**PENTING:** Set Document Root ke folder `/public`

### 3. Setup .env File

Buat file `.env` di root project (sejajar dengan composer.json):

```env
APP_NAME="Laravel Starter Kit"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 4. Install Composer Dependencies

Via SSH (jika ada akses SSH):
```bash
cd /path/to/your/project
composer install --optimize-autoloader --no-dev
```

Via cPanel Terminal:
```bash
composer install --optimize-autoloader --no-dev
```

Jika tidak ada akses composer di hosting, upload folder `vendor` dari local setelah menjalankan `composer install`.

### 5. Generate APP_KEY
```bash
php artisan key:generate
```

Atau manual di cPanel Terminal/SSH.

### 6. Set Permissions

Folder yang harus writable:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Via cPanel File Manager, klik kanan folder → Change Permissions → 775

### 7. Run Migrations
```bash
php artisan migrate --force
```

### 8. Seed Demo Data
```bash
php artisan db:seed --class=RolePermissionSeeder --force
```

### 9. Optimize untuk Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 10. Setup .htaccess

Di folder `public/`, pastikan ada `.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 🔒 Security Checklist

✅ Set `APP_DEBUG=false` di production
✅ Set `APP_ENV=production`
✅ Pastikan folder storage & bootstrap/cache writable
✅ Jangan upload file `.env` dari local
✅ Gunakan APP_KEY yang berbeda untuk tiap environment
✅ Set permissions yang benar (775 untuk storage)
✅ Document root HARUS di folder `/public`

## 🌐 Setup Database di cPanel

1. Buka **cPanel → MySQL Databases**
2. Buat database baru
3. Buat user baru dengan password
4. Assign user ke database dengan **ALL PRIVILEGES**
5. Catat nama database, username, dan password
6. Masukkan ke file `.env`

## 📝 Troubleshooting

### Error: 500 Internal Server Error
- Cek permissions folder storage & bootstrap/cache
- Cek file `.htaccess` ada di folder public
- Cek error log di cPanel

### Error: SQLSTATE Connection Refused
- Cek koneksi database di `.env`
- Pastikan DB_HOST biasanya `localhost`
- Cek kredensial database benar

### Error: The page isn't redirecting properly
- Clear cache: `php artisan cache:clear`
- Cek APP_URL di `.env` sesuai domain

### Halaman Tanpa Style
- ✅ Sudah menggunakan CDN, tidak perlu build assets
- Cek koneksi internet server bisa akses CDN
- Buka browser console, cek ada error loading CDN

## 🎯 Login Setelah Deploy

Akses: `https://yourdomain.com/login`

Gunakan akun demo:
- Email: `admin@example.com`
- Password: `password`

## 🔄 Update Aplikasi

Untuk update ke versi baru:

1. Backup database dan file `.env`
2. Upload file baru (overwrite yang lama)
3. Restore file `.env`
4. Run: `php artisan migrate --force`
5. Clear cache:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```
6. Re-optimize:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## 📞 Catatan Penting

- **TIDAK PERLU NPM** di hosting karena sudah pakai CDN
- **TIDAK PERLU node_modules** folder
- Pastikan PHP version minimal 8.2
- Pastikan extension yang required Laravel terinstall
- Document root HARUS di folder `/public`

## 🎨 Performa CDN

Keuntungan pakai CDN:
- ✅ Tidak perlu compile assets
- ✅ File lebih cepat di-load (dari CDN server terdekat)
- ✅ Browser caching otomatis
- ✅ Deployment lebih mudah (tidak perlu upload public/build)

Kekurangan:
- Butuh koneksi internet untuk load CSS/JS
- Tidak cocok untuk intranet/offline apps

Jika perlu offline, bisa download file CDN dan simpan di `public/assets/` lalu ubah link di layout.
