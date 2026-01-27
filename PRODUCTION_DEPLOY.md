# 🚀 Production Deployment Guide

Panduan ini menjelaskan langkah-langkah untuk deploy aplikasi Monitoring Project ke server production.

---

## ✅ Production Checklist

### 1. Prerequisites Server

- [ ] PHP 8.2 atau lebih tinggi
- [ ] Composer 2.x
- [ ] MySQL 8.0 / MariaDB 10.6+
- [ ] Web Server (Nginx/Apache)
- [ ] Node.js 18+ & NPM (untuk build assets)
- [ ] Git

### 2. Konfigurasi Environment

```bash
# Copy file environment
cp .env.production.example .env

# Generate application key
php artisan key:generate
```

**PENTING - Edit .env:**
```env
APP_ENV=production
APP_DEBUG=false          # ⚠️ WAJIB false di production!
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_DATABASE=monitoring_project
DB_USERNAME=your_user
DB_PASSWORD=your_password

# FTP Server
FTP_HOST=10.0.1.99
FTP_USERNAME=your_ftp_user
FTP_PASSWORD=your_ftp_password
FTP_SOURCE_DIR=/CSV
FTP_PROCESSED_DIR=/Processed
FTP_ERROR_DIR=/Error
```

### 3. Install Dependencies

```bash
# Install PHP dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Install Node dependencies & build assets
npm ci
npm run build
```

### 4. Database Setup

```bash
# Jalankan migrations
php artisan migrate --force

# Jalankan seeders (jika diperlukan)
php artisan db:seed --force
```

### 5. Optimize Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Create storage link
php artisan storage:link
```

### 6. Set Permissions

```bash
# Linux/macOS
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Pastikan folder log bisa ditulis
chmod -R 775 storage/logs
```

---

## ⏰ Setup Scheduler (Auto Import SAP)

Scheduler Laravel perlu dijalankan agar auto-import SAP berjalan otomatis.

### Linux (Cron Job)

Tambahkan cron job untuk user web server:

```bash
# Edit crontab
crontab -e

# Tambahkan baris ini:
* * * * * cd /path/to/monitoring-project && php artisan schedule:run >> /dev/null 2>&1
```

### Windows (Task Scheduler)

1. Buka **Task Scheduler**
2. Create Basic Task:
   - Name: `Laravel Scheduler - Monitoring Project`
   - Trigger: Daily, repeat every 1 minute
   - Action: Start a program
   - Program: `php`
   - Arguments: `artisan schedule:run`
   - Start in: `C:\path\to\monitoring-project`

Atau gunakan PowerShell script:

```powershell
# scheduler-runner.ps1
Set-Location "C:\path\to\monitoring-project"
while ($true) {
    php artisan schedule:run
    Start-Sleep -Seconds 60
}
```

---

## 🌐 Konfigurasi Web Server

### Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/monitoring-project/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache (.htaccess sudah ada di /public)

Pastikan `mod_rewrite` aktif:
```bash
a2enmod rewrite
systemctl restart apache2
```

---

## 🔒 Security Checklist

- [ ] `APP_DEBUG=false` di .env
- [ ] `APP_ENV=production` di .env
- [ ] HTTPS enabled dengan SSL certificate
- [ ] Folder `.env` tidak bisa diakses publik
- [ ] Folder `storage/` tidak bisa diakses publik
- [ ] Database password kuat & unik
- [ ] FTP credentials aman
- [ ] Rate limiting aktif (sudah ada di Laravel)
- [ ] CSRF protection aktif (default Laravel)

---

## 📊 Monitoring & Maintenance

### Cek Log

```bash
# Log aplikasi
tail -f storage/logs/laravel.log

# Log auto-import SAP
tail -f storage/logs/sap-auto-import.log

# Log error SAP
ls -la storage/logs/sap_import_errors_*.log
```

### Test Koneksi FTP

```bash
php artisan tinker
>>> app(\App\Services\FtpService::class)->testConnection();
```

### Manual Trigger Auto Import

```bash
# Jalankan auto import manual (untuk testing)
php artisan sap:auto-import

# Dengan force import (import ulang file yang sudah ada)
php artisan sap:auto-import --force
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 Troubleshooting

### FTP Connection Failed

1. Pastikan FTP server bisa diakses dari server production
2. Cek firewall tidak memblokir port 21
3. Test koneksi manual:
   ```bash
   telnet 10.0.1.99 21
   ```

### Scheduler Tidak Berjalan

1. Cek cron job aktif: `crontab -l`
2. Cek log scheduler: `storage/logs/sap-auto-import.log`
3. Test manual: `php artisan schedule:run`

### Permission Denied

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .
```

### Database Connection Error

1. Cek credentials di .env
2. Cek database service aktif
3. Test koneksi: `php artisan migrate:status`

---

## 📝 Informasi Scheduler

| Task | Interval | Deskripsi |
|------|----------|-----------|
| `sap:auto-import` | Every 5 minutes | Import otomatis file CSV dari FTP |

File sukses dipindahkan ke: `/Processed/`  
File error dipindahkan ke: `/Error/`

---

## 🎯 Quick Deploy Commands

```bash
# One-liner untuk deploy update
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
npm ci && npm run build && \
php artisan migrate --force && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache

# Restart services (Linux)
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## ✨ Status: PRODUCTION READY

Aplikasi ini sudah siap untuk production dengan catatan:

1. ✅ Auto-import SAP berjalan otomatis via scheduler
2. ✅ File dipindahkan setelah import (Processed/Error)
3. ✅ Logging lengkap untuk audit
4. ✅ UI monitoring tanpa fitur manual import
5. ✅ Error handling sudah diterapkan
6. ⚠️ Pastikan setup scheduler/cron di server
7. ⚠️ Pastikan `APP_DEBUG=false` di production
