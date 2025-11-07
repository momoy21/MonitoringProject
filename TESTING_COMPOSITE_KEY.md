# Test Composite Key - History Proyek

## Test Case 1: Create History Proyek
1. Buka browser dev tools (F12) -> Console tab
2. Navigate ke: `/dataproyek/{id_project}/add-history`
3. Isi form dengan data lengkap
4. Klik Submit
5. Lihat console untuk error

**Expected**: Redirect ke `/dataproyek/{id_project}` dengan message success
**Check di console**: Harus tidak ada error 422 atau 500

---

## Test Case 2: Edit History Proyek
1. Navigate ke: `/dataproyek/{id_project}` (detail page)
2. Double-click pada kolom "No." (norut) untuk history yang ingin di edit
3. Form edit harus terbuka di `/dataproyek/history/{id_project}/{norut}/edit`
4. Ubah data (misalnya: Nama Proyek atau Nilai Proyek)
5. Klik Submit
6. Lihat console untuk error

**Expected**: Redirect ke `/dataproyek/{id_project}` dengan message success
**Check di console**: Harus tidak ada error 422 atau 500

---

## Test Case 3: Download Dokumen History
1. Navigate ke: `/dataproyek/{id_project}` (detail page)
2. Klik tombol "Aksi" -> "Lihat Detail" pada history yang memiliki dokumen
3. Modal detail terbuka
4. Klik tombol "Download Dokumen" di bagian bawah modal
5. File harus ter-download

**Expected**: File dokumen ter-download
**Check**: Tidak ada error 404 atau 500

---

## Common Issues & Solutions

### Issue 1: Error 422 (Validation Error)
**Symptom**: Form tidak submit, ada pesan "Mohon perbaiki kesalahan pada form"
**Check**:
- Buka Console -> Network tab -> Cari request POST/PUT yang gagal
- Klik request tersebut -> Preview tab -> Lihat field mana yang error
- Pastikan semua field required terisi dengan benar

**Possible Causes**:
- Field hidden tidak terkirim (id_konsumen, id_bidjasa, etc.)
- Format tanggal salah
- Nilai proyek format salah

### Issue 2: Error 500 (Server Error)
**Symptom**: Form submit tapi ada error dari server
**Check**:
- Laravel log: `storage/logs/laravel.log`
- Cari error terakhir dengan timestamp sesuai waktu submit

**Possible Causes**:
- Composite key constraint violation
- Foreign key constraint violation
- File upload error

### Issue 3: Download Error
**Symptom**: Download dokumen error 404 atau 500
**Check**:
- URL download di browser: Harus `/dataproyek/download/{id_project}?norut={norut}&v={timestamp}`
- Pastikan parameter `norut` ada di URL untuk history proyek
- Check file exists: `storage/app/public/dokumen_proyek/`

### Issue 4: Double-click Edit Tidak Bekerja
**Symptom**: Klik 2x pada norut tidak membuka form edit
**Check**:
- Console -> Errors tab -> Cari error JavaScript
- Pastikan function `editHistoryProyek()` ada di global scope
- Check URL yang dihasilkan

---

## Debugging Steps

### Step 1: Check Routes
```bash
php artisan route:list --path=dataproyek/history
```

Expected output:
- `PUT dataproyek/history/{idProject}/{norut}`
- `DELETE dataproyek/history/{idProject}/{norut}`
- `GET dataproyek/history/{idProject}/{norut}/edit`

### Step 2: Check Model
Open tinker:
```bash
php artisan tinker
```

Test composite key:
```php
// Find history by composite key
$history = \App\Models\HistoryProyek::where('id_project', 'YOUR_ID')
    ->where('norut', 1)
    ->first();

// Should return the record
dd($history);

// Test primary key
dd($history->getKey());
// Should return: ['norut' => 1, 'id_project' => 'YOUR_ID']
```

### Step 3: Check Form Submission
In browser console, before submitting:
```javascript
// Check form data
const formData = new FormData($('#proyekForm')[0]);
for (let [key, value] of formData.entries()) {
    console.log(key, ':', value);
}

// Check form URL
console.log('Form URL:', $('#proyekForm').attr('action'));
```

### Step 4: Check AJAX Response
In browser Network tab:
1. Submit form
2. Find POST/PUT request
3. Click request -> Response tab
4. Check for errors or validation messages

---

## Quick Fix Commands

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Restart Server
```bash
# Stop server (Ctrl+C)
# Start again
php artisan serve
```

### Check Permissions
```bash
# Windows PowerShell
icacls storage /grant Everyone:F /t
icacls bootstrap/cache /grant Everyone:F /t
```

---

## Expected Behavior

### Create History:
1. User klik "Tambah History" button
2. Form terbuka dengan id_project dari parent project
3. Fields readonly: Cost Center, Konsumen, Bidang Jasa, Kondisi, Lokasi, Jarak
4. Fields editable: Dokumen IO, Nama Proyek, No Kontrak, dates, Nilai, Status, Keterangan
5. Submit -> Auto generate `norut` (sequence per id_project)
6. Redirect ke show page dengan success message

### Edit History:
1. User double-click pada norut column
2. Form edit terbuka dengan URL: `/dataproyek/history/{id_project}/{norut}/edit`
3. Form pre-filled dengan data existing
4. Submit -> Update record using composite key
5. Redirect ke show page dengan success message

### Download:
1. User klik "Lihat Detail" pada history
2. Modal terbuka dengan detail lengkap
3. Jika ada dokumen, tampil tombol "Download Dokumen"
4. URL download: `/dataproyek/download/{id_project}?norut={norut}`
5. File ter-download

---

## Contact for Debug

If still error after all checks:
1. Copy error message dari Console
2. Copy error dari `storage/logs/laravel.log` (last 50 lines)
3. Screenshot Network tab showing failed request
4. Provide: What action? What data? What error?
