# Seeder Documentation - Form Builder System

## Overview

Semua seeder telah dibuat untuk mengisi database dengan data sample yang lengkap dan realistis. Total 9 seeder files + 1 main DatabaseSeeder.

## Seeder Files Created

### 1. **CategorySeeder.php** ✅

**Isi Data:**

-   6 kategori form yang berbeda
-   Kategori: Pendaftaran Siswa, Beasiswa, Event & Kompetisi, Survey, Lowongan Kerja, Kontak & Layanan
-   Setiap kategori memiliki icon, color, dan description

**Data Sample:**

```php
- Pendaftaran Siswa (school icon, blue)
- Beasiswa (award icon, green)
- Event & Kompetisi (trophy icon, orange)
- Survey (clipboard icon, purple)
- Lowongan Kerja (briefcase icon, red)
- Kontak & Layanan (mail icon, indigo)
```

---

### 2. **FormSeeder.php** ✅

**Isi Data:**

-   4 form lengkap dengan berbagai konfigurasi
-   Form dengan payment: Pendaftaran Siswa, Lomba Karya Tulis, Kelas Coding
-   Form tanpa payment: Beasiswa

**Forms Created:**

1. **Pendaftaran Siswa Baru 2024/2025**

    - Enable payment ✓
    - Enable affiliate ✓
    - Max submissions: 1000
    - Duration: 3 bulan

2. **Beasiswa Prestasi Akademik 2024**

    - Enable payment ✗
    - Enable affiliate ✗
    - Max submissions: 500
    - Require login ✓

3. **Lomba Karya Tulis Ilmiah 2024**

    - Enable payment ✓
    - Enable affiliate ✓
    - Max submissions: 300
    - Duration: 1 bulan

4. **Daftar Kelas Online Coding**
    - Enable payment ✓
    - Enable affiliate ✓
    - Max submissions: unlimited
    - Duration: 6 bulan

---

### 3. **SectionSeeder.php** ✅

**Isi Data:**

-   11 section yang tersebar di 4 form
-   Setiap section memiliki title, description, dan order

**Sections per Form:**

-   **Pendaftaran Siswa**: 4 sections (Data Pribadi, Data Orang Tua, Riwayat Pendidikan, Dokumen)
-   **Beasiswa**: 3 sections (Identitas, Prestasi, Kondisi Ekonomi)
-   **Lomba**: 2 sections (Data Peserta, Karya Ilmiah)
-   **Coding**: 2 sections (Informasi Pendaftar, Latar Belakang & Minat)

---

### 4. **FieldSeeder.php** ✅

**Isi Data:**

-   60+ fields dengan berbagai tipe input
-   10+ field types: text, email, tel, date, number, textarea, select, radio, checkbox, file

**Field Types Demonstrated:**

-   **Text**: Nama, NIK, Institusi, dll
-   **Email**: Email validation
-   **Tel**: Nomor telepon dengan regex validation
-   **Date**: Tanggal lahir
-   **Number**: Usia, IPK, Nilai, dll
-   **Textarea**: Alamat, Motivasi, Abstrak
-   **Select**: Provinsi, Pekerjaan, Penghasilan, Jadwal
-   **Radio**: Jenis Kelamin, Pengalaman
-   **Checkbox**: Multiple bahasa pemrograman
-   **File**: Upload foto, KTP, ijazah, sertifikat, karya tulis

**Validation Rules:**

-   Min/Max length
-   Digits validation (NIK: 16, NISN: 10)
-   Regex patterns (nomor telepon)
-   File mimes and size limits
-   Numeric ranges (IPK: 0-4, Nilai: 0-100)

---

### 5. **PricingTierSeeder.php** ✅

**Isi Data:**

-   8 pricing tiers untuk 3 forms berbeda
-   Berbagai harga: Rp 150.000 - Rp 1.200.000

**Pricing Breakdown:**

1. **Pendaftaran Siswa**

    - Regular: Rp 500.000
    - Early Bird: Rp 350.000 (diskon 30%)

2. **Lomba Karya Tulis**

    - Individu: Rp 150.000
    - Tim (3 orang): Rp 400.000

3. **Kelas Coding**
    - Basic (1 bulan): Rp 250.000
    - Intermediate (3 bulan): Rp 650.000
    - Advanced (6 bulan): Rp 1.200.000

**Features Array:**
Setiap tier memiliki daftar fitur yang didapat

---

### 6. **UpsellSeeder.php** ✅

**Isi Data:**

-   12 upsell products untuk 3 forms
-   Harga: Rp 50.000 - Rp 750.000
-   Semua dengan image placeholder

**Upsells per Form:**

1. **Pendaftaran Siswa** (4 items):

    - Buku Panduan: Rp 75.000
    - Seragam Olahraga: Rp 250.000
    - Tas Sekolah: Rp 350.000
    - Alat Tulis: Rp 150.000

2. **Lomba** (3 items):

    - Workshop: Rp 200.000
    - Review by Juri: Rp 100.000
    - E-Book: Rp 50.000

3. **Kelas Coding** (4 items):
    - Private Mentoring: Rp 500.000
    - Lifetime Access: Rp 300.000
    - Developer Tools: Rp 750.000
    - Portfolio Website: Rp 400.000

---

### 7. **AffiliateRewardSeeder.php** ✅

**Isi Data:**

-   3 affiliate users created
-   8 affiliate reward records
-   Mix of percentage and fixed commission

**Affiliate Users:**

1. **Ahmad Affiliate** (ahmad.affiliate@example.com)

    - Code: AHMAD2024
    - Pendaftaran: 10% commission, 10 referrals, Rp 350.000 earned
    - Coding: 15% commission, 5 referrals, Rp 975.000 earned

2. **Siti Marketer** (siti.marketer@example.com)

    - Code: SITI10
    - Pendaftaran: Fixed Rp 25.000, 25 referrals, Rp 625.000 earned
    - Lomba: 20% commission, 15 referrals, Rp 450.000 earned

3. **Budi Partner** (budi.partner@example.com)
    - Code: BUDI123
    - Coding: 12% commission, 8 referrals, Rp 1.440.000 earned
    - Lomba: Fixed Rp 15.000, 12 referrals, Rp 180.000 earned

**New Affiliates** (no earnings yet):

-   NEWAFFILIATE, CODEMASTER

---

### 8. **SubmissionSeeder.php** ✅

**Isi Data:**

-   10 submissions dengan berbagai status
-   Payment status: paid, pending
-   Status: approved, pending
-   Complete form data dengan nested arrays

**Submissions Breakdown:**

1. **Pendaftaran Siswa** (3 submissions):

    - PSBA-2024-0001: Paid, Approved (with upsells + affiliate)
    - PSBA-2024-0002: Pending, Pending
    - PSBA-2024-0003: Paid, Pending

2. **Beasiswa** (2 submissions):

    - BPA-2024-0001: Approved (no payment)
    - BPA-2024-0002: Pending (no payment)

3. **Lomba** (1 submission):

    - LKTI-2024-0001: Paid, Approved (with affiliate)

4. **Kelas Coding** (3 submissions):
    - CODE-2024-0001: Paid, Approved (Basic tier)
    - CODE-2024-0002: Paid, Approved (Intermediate + upsells + affiliate)
    - CODE-2024-0003: Pending, Pending (Advanced tier)

**Total Amounts:**

-   Terkecil: Rp 150.000
-   Terbesar: Rp 1.450.000 (dengan upsells)

---

### 9. **AnnouncementSeeder.php** ✅

**Isi Data:**

-   9 announcements dengan berbagai status
-   Status: accepted, rejected, pending
-   Rich result_data dengan array dan nested objects

**Announcements Created:**

1. **Pendaftaran Siswa** (3 items):

    - PSBA-2024-0001: Diterima di Kelas X IPA 1
    - PSBA-2024-0003: Diterima di Kelas X IPA 2
    - PSBA-2024-0099: Dalam proses verifikasi

2. **Beasiswa** (2 items):

    - BPA-2024-0001: Lolos (Beasiswa Rp 20jt/tahun)
    - BPA-2024-0099: Tidak lolos

3. **Lomba** (1 item):

    - LKTI-2024-0001: Juara 2 (Hadiah Rp 5jt)

4. **Kelas Coding** (2 items):
    - CODE-2024-0001: Batch 15 Beginner
    - CODE-2024-0002: Batch 12 Intermediate

**Result Data Examples:**

-   Langkah selanjutnya (array)
-   Nilai karya (nested scores)
-   Jadwal kelas & link Zoom
-   Hadiah lomba (object)

---

### 10. **DatabaseSeeder.php** (Updated) ✅

**Changes:**

-   Added all 9 seeders in correct order
-   Respects foreign key constraints
-   Creates 2 users (admin & test)
-   Success message with emojis

**Execution Order:**

```
1. CategorySeeder       → Creates categories
2. FormSeeder           → Creates forms (FK: category_id)
3. SectionSeeder        → Creates sections (FK: form_id)
4. FieldSeeder          → Creates fields (FK: section_id)
5. PricingTierSeeder    → Creates pricing (FK: form_id)
6. UpsellSeeder         → Creates upsells (FK: form_id)
7. AffiliateRewardSeeder → Creates affiliates (FK: form_id, user_id)
8. SubmissionSeeder     → Creates submissions (FK: form_id, pricing_tier_id, etc)
9. AnnouncementSeeder   → Creates announcements (FK: form_id, submission_id)
```

---

## How to Run Seeders

### Run All Seeders

```bash
php artisan db:seed
```

### Run Specific Seeder

```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=FormSeeder
php artisan db:seed --class=FieldSeeder
# etc...
```

### Fresh Migration + Seed

```bash
php artisan migrate:fresh --seed
```

### Refresh Database + Seed

```bash
php artisan migrate:refresh --seed
```

---

## Data Statistics

**Total Records Created:**

-   ✅ 6 Categories
-   ✅ 4 Forms
-   ✅ 11 Sections
-   ✅ 60+ Fields (dengan 10+ field types)
-   ✅ 8 Pricing Tiers
-   ✅ 12 Upsells
-   ✅ 5 Users (2 regular + 3 affiliates)
-   ✅ 8 Affiliate Rewards
-   ✅ 10 Submissions (berbagai payment status)
-   ✅ 9 Announcements (accepted, rejected, pending)

**Total: 130+ database records** dengan data realistis!

---

## Sample Data Features

### ✅ Complete Form Journey

1. Category → Form → Sections → Fields
2. Pricing Tiers untuk berbagai paket
3. Upsells untuk additional products
4. Affiliate system dengan commission tracking
5. Public submissions dengan payment
6. Announcements untuk hasil seleksi

### ✅ Real-World Scenarios

-   Forms dengan payment & tanpa payment
-   Affiliate dengan percentage & fixed commission
-   Submissions dengan berbagai status (paid, pending, approved)
-   Upsells dengan harga bervariasi
-   Announcements dengan rich data (accepted/rejected)

### ✅ Data Validation Examples

-   NIK validation (16 digits)
-   NISN validation (10 digits)
-   Email validation
-   Phone regex validation (08xxxxxxxxxx)
-   File upload validation (mimes, max size)
-   Numeric ranges (IPK 0-4, Nilai 0-100)

### ✅ Indonesian Context

-   Provinsi Indonesia
-   Pekerjaan orang tua
-   Range penghasilan dalam Rupiah
-   Nomor telepon format Indonesia
-   Bahasa pemrograman populer
-   Jadwal belajar sesuai konteks lokal

---

## Testing Recommendations

### 1. Test Complete Workflow

```bash
# 1. Fresh migration + seed
php artisan migrate:fresh --seed

# 2. Test public form endpoints
GET /api/public/forms/pendaftaran-siswa-baru-2024-2025

# 3. Test submission with payment
POST /api/public/submissions

# 4. Test announcement check
GET /api/public/announcements/check?identifier=PSBA-2024-0001
```

### 2. Test Admin Features

```bash
# Login as admin
POST /api/login
{
  "email": "admin@example.com",
  "password": "password"
}

# Get all forms
GET /api/forms

# Get submissions with filters
GET /api/forms/{uuid}/submissions?status=approved&payment_status=paid
```

### 3. Test Affiliate System

```bash
# Submit with affiliate code
POST /api/public/submissions
{
  "affiliate_code": "AHMAD2024",
  ...
}

# Check affiliate stats
GET /api/forms/{uuid}/affiliate-rewards
```

---

## Notes & Best Practices

### ⚠️ Important Notes

1. **Foreign Keys**: Seeders harus dijalankan sesuai urutan karena foreign key constraints
2. **UUIDs**: Semua model menggunakan UUID sebagai primary key
3. **Password**: Semua user dibuat dengan password "password" (hanya untuk development)
4. **Images**: Menggunakan placeholder dari picsum.photos

### 🎯 Production Considerations

1. **Don't run in production**: Seeders ini hanya untuk development/testing
2. **Change passwords**: Ganti semua password sebelum deploy
3. **Real images**: Replace placeholder images dengan real images
4. **Email verification**: Enable email verification untuk production
5. **Payment gateway**: Integrate dengan real payment gateway

### 💡 Tips

1. Use `php artisan migrate:fresh --seed` untuk reset database
2. Modify seeder data sesuai kebutuhan
3. Add more sample data jika diperlukan
4. Test all workflows dengan data seed ini

---

## Success! ✅

Semua 10 seeders berhasil dibuat dengan:

-   ✅ Data realistis dan lengkap
-   ✅ Foreign key relationships yang benar
-   ✅ Berbagai field types dan validations
-   ✅ Complete form submission workflow
-   ✅ Payment & affiliate system
-   ✅ Rich announcement data
-   ✅ Indonesian context

**Ready to seed database:**

```bash
php artisan migrate:fresh --seed
```
