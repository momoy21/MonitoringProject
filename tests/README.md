# Test Suite - MonitoringProject

## Overview
Comprehensive test suite using Pest 3 for Laravel 12 MonitoringProject.

## Current Status
- **75 failed, 133 passed** (273 assertions)
- Target: ~80+ test cases

## Test Configuration

### Database
- Uses MySQL (`monitoring_project_test`)
- Configured in `phpunit.xml`

### Running Tests
```bash
php artisan test
```

## Error Explanations

### 1. Laravel Breeze Auth Tests
**Status:** Failing

**Error:** `Route [verification.send] not defined`

**Cause:** 
- Default Laravel Breeze tests expect email verification routes
- Routes not configured in this project

**Affected Tests:**
- `EmailVerificationTest`
- `PasswordConfirmationTest`
- `PasswordResetTest`
- `PasswordUpdateTest`
- `RegistrationTest`
- `ProfileTest`

**Solution:** These tests are optional - the app doesn't use email verification.

---

### 2. Missing Views (CRUD Show)
**Status:** Failing

**Error:** `Expected 200, got 302` (redirect)

**Cause:** 
- No `show.blade.php` views exist for most resources
- Controller returns redirect to index instead of showing view

**Affected Tests:**
- `BidangJasaCrudTest::it can show bidang jasa`
- `DataPeluangCrudTest::it can show data peluang`
- `JenisProyekCrudTest::it can show jenis proyek`
- `KonsumenCrudTest::it can show konsumen`
- `MasterDivisiCrudTest::it can show master divisi`
- `KondisiProyekCrudTest::it can show kondisi proyek`

**Solution:** Create `show.blade.php` views or update tests to check redirect behavior.

---

### 3. Factory ID Generation Conflicts
**Status:** Failing

**Error:** `UniqueConstraintViolationException` - Duplicate entry

**Cause:**
- Multiple tests creating records without unique IDs
- Static sequence in factories not resetting between tests

**Affected:**
- `BidangJasaFactory` - generates IDs 90+
- `KonsumenFactory` - generates K00001 format
- `DataPeluangFactory` - generates 0001 format

**Solution:** Each test should use `sequenceId()` method or factory should use `unique()` in definition.

---

### 4. Database Constraint Errors
**Status:** Failing

**Error:** 
- `String data, right truncated: 1406 Data too long for column 'desc_bidjasa'`
- Foreign key constraint failures

**Cause:**
- Factory generates text longer than column size (50 chars)
- Related records not created before testing foreign keys

**Solution:** 
- Constrain factory to use shorter strings (max 50 chars)
- Use `has()` or `for()` relationship methods in tests

---

### 5. Migration Seed Data Conflict
**Status:** Resolved

**Issue:** 
- `2025_08_25_041344_create_bidangjasa_table.php` inserted 8 records during migration
- Caused test isolation issues

**Fix Applied:**
- Removed seed data from migration
- Data should be seeded via DatabaseSeeder instead

---

## Test Structure

```
tests/
├── Feature/
│   ├── AuthorizationTest.php
│   ├── BidangJasaCrudTest.php
│   ├── DataPeluangCrudTest.php
│   ├── JenisProyekCrudTest.php
│   ├── KaryawanCrudTest.php
│   ├── KondisiProyekCrudTest.php
│   ├── KonsumenCrudTest.php
│   └── MasterDivisiCrudTest.php
├── Unit/
│   ├── BidangJasaTest.php
│   ├── DataPeluangTest.php
│   ├── JenisProyekTest.php
│   ├── KaryawanTest.php
│   ├── KondisiProyekTest.php
│   ├── KonsumenTest.php
│   ├── MasterDivisiTest.php
│   ├── SpecRabDetailTest.php
│   ├── SpesifikasiRABTest.php
│   └── UserTest.php
├── Traits/
│   └── TestHelpers.php
└── Pest.php
```

## Working Tests

### Authorization Tests (Working)
- Guest access redirects to login
- PM gets 403 on Super Admin routes
- PM can access shared routes
- Super Admin can access all routes

### CRUD Tests (Partially Working)
- Index pages accessible
- Create pages accessible  
- Store operations working (some)
- Update operations working (some)
- Delete operations working (some)

### Model Unit Tests (Most Working)
- ID generation methods
- Scope filters (active, inactive, search)
- Accessors (status_label, alamat_lengkap, etc.)
- Relationships

## Next Steps

1. **Fix Factories:** Ensure unique ID generation per test
2. **Create Views:** Add show.blade.php for resources
3. **Skip Optional Breeze Tests:** Remove or skip auth tests not used
4. **Add Test Isolation:** Use transactions or truncate between tests
