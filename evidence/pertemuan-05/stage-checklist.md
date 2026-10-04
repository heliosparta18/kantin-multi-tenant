# Stage Checklist — Modul 5: Administrasi Kantin, Tenant, Role & Skema Komisi

Berikut adalah daftar verifikasi pelaksanaan seluruh tahapan (Tahap 1–8) sesuai panduan praktikum Pertemuan 05:

## Checklist Tahapan Praktikum

- [x] **Tahap 1 — Database & Schema Design**:
  - Migrasi `user_canteen_roles`, `user_tenant_roles`, `tenant_balances`, `commission_schemes`, `tenant_bank_accounts`, `audit_logs`.
  - Penambahan kolom `display_name` dan soft deletes pada tabel `tenants`.
  - Virtual generated columns `active_lock` dan `primary_lock` dengan constraint UNIQUE untuk perlindungan tingkat database (DB guards).
  - Seeder demo (`DemoCanteenSeeder`) diperbarui untuk relasi M:N peran kantin dan tenant.

- [x] **Tahap 2 — Domain Models & Factories**:
  - Model `UserCanteenRole`, `UserTenantRole`, `TenantBalance`, `CommissionScheme`, `TenantBankAccount`, `AuditLog`.
  - Relasi Eloquent lengkap pada `Tenant`, `Canteen`, `User`, `Menu`.
  - Mutator/casting enkripsi untuk nomor rekening bank (`encrypted`).
  - Factory: `CanteenFactory`, `TenantFactory`, `CommissionSchemeFactory`, `MenuFactory`, `UserFactory`.

- [x] **Tahap 3 — Core Services (Business Logic Layer)**:
  - `AuditLogger`: Pencatatan aktivitas sensitif append-only dengan sanitasi data otomatis.
  - `CreateTenant`: Onboarding atomik (tenant, saldo awal, komisi awal, owner user, primary bank account, reset password link via `DB::afterCommit`).
  - `ChangeCommissionSchedule`: Penjadwalan komisi efektif berinterval `[valid_from, valid_to)` dengan pencegahan overlap dan `lockForUpdate`.
  - `ManageBankAccount`: Enkripsi rekening, ekstraksi 4 digit terakhir (`account_last4`), dan penjaminan single-primary bank account.
  - `AssignTenantRole`: Pemberian dan pencabutan peran tenant dengan proteksi larangan menghapus owner terakhir (*cannot remove last owner*).
  - `ChangeTenantStatus`: Transisi status tenant (`active` <-> `inactive`) dengan pencatatan audit.

- [x] **Tahap 4 — Authorization & Policies (UC-19 & UC-21)**:
  - `TenantPolicy`: Otorisasi terpusat berbasis peran kantin aktor (`managesCanteen`: owner, manager, finance).
  - Isolasi multi-kantin: Pengelola kantin A dilarang memanipulasi tenant kantin B (HTTP 403 Forbidden).

- [x] **Tahap 5 — Web Architecture & Modular Routing**:
  - `app/Support/Routing/PortalRoutes.php`: Definisi rute portal modular (`PortalRoutes::admin()`, `PortalRoutes::tenant()`).
  - `ModuleServiceProvider` & `AdminServiceProvider`: Registrasi modul admin mandiri.
  - Form Requests: `StoreTenantRequest`, `ScheduleCommissionRequest`, `StoreBankAccountRequest` dengan validasi ketat.
  - Controllers: `AdminTenantController`, `AdminCommissionController`, `AdminTenantRoleController`, `AdminBankAccountController`, `AdminTenantStatusController`.

- [x] **Tahap 6 — Blade Views & UI Components**:
  - Tampilan modular `admin::tenants.index`, `admin::tenants.create`, `admin::tenants.edit`.
  - Integrasi komponen Tailwind: tombol aksi, status badge interaktif (berwarna hijau untuk AKTIF, merah untuk NONAKTIF).
  - Tampilan riwayat komisi dengan identitas pengelola pengubah.

- [x] **Tahap 7 — Dokumentasi & Quality Gate**:
  - Penyusunan `demo-script.md`, `stage-checklist.md`, `security-verification.md`, `quality-gate.md`, `known-issues.md`.
  - Export hasil tes PHPUnit ke folder `evidence/pertemuan-05/test-results/`.

- [x] **Tahap 8 — Penyempurnaan UC-19 & UC-21**:
  - `LoginLockout`: Rate limiting 5 kali percobaan gagal per akun dalam 10 menit mengunci login selama 15 menit (tahan terhadap pergantian IP).
  - Pesan error login generik (*"Surel atau kata sandi tidak sesuai."*) mencegah enumerasi akun (account enumeration attack).
  - Session lifetime 480 menit (8 jam nonaktif).
  - `DashboardRedirectController`: Pengalihan otomatis sesuai peran aktor setelah login (admin -> admin portal, operator -> tenant dashboard, customer -> dashboard umum).
  - Filter katalog publik: Tenant nonaktif disembunyikan otomatis dari katalog pelanggan tanpa kehilangan data riwayat.

## Hasil Pengujian Otomasi
- Test Suite Pertemuan 05: **28 passed (111 assertions, 0 failures)**.
- Test Suite Seluruh Aplikasi: **74 passed, 1 skipped, 1 risky, 0 failures (206 assertions)**.
