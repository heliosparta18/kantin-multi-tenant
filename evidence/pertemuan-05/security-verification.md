# Security Verification — Modul 5: Administrasi Kantin & Tenant

Dokumen ini memverifikasi implementasi kontrol keamanan, privasi data finansial, pembatasan otorisasi, dan proteksi otentikasi berdasarkan requirement SRS v2.

---

## 1. Otorisasi Akses & Isolasi Antar-Kantin (UC-19)
- **Aturan**: Hanya user yang memiliki peran pengelola (`owner`, `manager`, `finance`) pada kantin terkait yang berhak mengakses dan mengubah data tenant. User tanpa peran kantin atau pengelola dari kantin lain wajib ditolak dengan respons `HTTP 403 Forbidden`.
- **Implementasi**: `App\Policies\TenantPolicy` memusatkan logika `managesCanteen($user, $tenant->canteen_id)`.
- **Verifikasi Uji Otomasi**:
  - `AdminManagementTest::test_only_canteen_manager_can_see_tenant_index`:
    - Admin dengan `UserCanteenRole` -> status 200 OK.
    - Admin luar tanpa peran kantin terkait -> status 403 Forbidden.
  - `TenantOnboardingTest::test_manager_of_other_canteen_cannot_change_status_and_status_is_whitelisted`:
    - Pengelola kantin B mencoba mengubah status tenant milik kantin A -> status 403 Forbidden.

---

## 2. Enkripsi Rekening Bank & Masking UI/Audit
- **Aturan**: Nomor rekening bank merupakan data sensitif (PII/Finansial). Nomor rekening mentah tidak boleh tersimpan dalam bentuk teks biasa (plaintext) di tabel database, tidak boleh muncul utuh di antarmuka web, dan tidak boleh tercatat di log audit.
- **Implementasi**:
  - Kolom `account_number_cipher` di tabel `tenant_bank_accounts` dienkripsi menggunakan key aplikasi Laravel (`casts = ['account_number_cipher' => 'encrypted']`).
  - Kolom `account_last4` menyimpan 4 digit terakhir nomor rekening untuk keperluan verifikasi tampilan UI (misal: `••6721`).
  - `AuditLogger` secara otomatis melakukan redaksi/sanitasi terhadap atribut sensitif (`account_number`, `password`, `account_number_cipher`, `token`, dll.) sebelum disimpan ke tabel `audit_logs`.
- **Verifikasi Uji Otomasi**:
  - `AdminManagementTest::test_bank_account_is_stored_encrypted_with_last4`:
    - String nomor rekening mentah (`1234567890`) dipastikan TIDAK ditemukan pada query mentah basis data (`assertStringNotContainsString`).
    - Atribut tersamar `account_last4` bernilai tepat `'7890'`.
    - Dekripsi Eloquent berhasil mengembalikan nomor asli ketika dipanggil secara resmi di aplikasi.
  - `AdminManagementTest::test_audit_log_is_written_on_sensitive_action`:
    - Log audit mencatat entitas tenant dan komisi tanpa membocorkan nomor rekening ke kolom `after`/`before`.

---

## 3. Rate Limiting & Account Lockout (UC-19 Alur 2b)
- **Aturan**:
  - Kegagalan login 5 kali dalam rentang 10 menit menyebabkan akun terkunci selama 15 menit.
  - Penghitungan kegagalan berbasis identitas akun (hash email), bukan hanya berbasis alamat IP klien.
  - Pesan penolakan wajib generik (*"Surel atau kata sandi tidak sesuai."*) untuk mencegah serangan enumerasi akun (user enumeration).
- **Implementasi**:
  - Service `App\Support\Auth\LoginLockout` terintegrasi dengan pipeline otentikasi `FortifyServiceProvider`.
  - Rate limiting berbasis cache dengan TTL 15 menit saat ambang batas tercapai.
- **Verifikasi Uji Otomasi**:
  - `LoginLockoutTest::test_five_failed_logins_within_ten_minutes_locks_account_for_fifteen_minutes`:
    - Percobaan 1–5: Gagal dengan pesan generik.
    - Percobaan 6: Ditolak dengan status terkunci sementara (15 menit), meskipun kredensial benar.
  - `LoginLockoutTest::test_lockout_persists_across_ip_changes`:
    - Mengubah IP dari `192.168.1.1` ke `192.168.1.2` tidak mereset status penguncian akun yang telah mencapai batas.
  - `LoginLockoutTest::test_inactive_account_is_rejected_with_generic_message`:
    - Akun nonaktif/suspended ditolak dengan pesan yang sama persis seperti kata sandi salah.

---

## 4. Keamanan Manajemen Sesi (Session Security)
- **Aturan**: Sesi pengguna kedaluwarsa setelah 8 jam (480 menit) tidak aktif untuk mengurangi risiko pembajakan sesi (session hijacking) di komputer bersama / kasir kantin.
- **Implementasi**: Konfigurasi `SESSION_LIFETIME=480` di `.env`, `.env.example`, dan `config/session.php`.
- **Verifikasi Uji Otomasi**:
  - `LoginLockoutTest::test_session_expires_after_eight_hours_of_inactivity`:
    - Berhasil memvalidasi nilai konfigurasi `config('session.lifetime') === 480`.

---

## 5. Integritas Basis Data & DB Guards
- **Aturan**:
  - Setiap tenant hanya boleh memiliki maksimal satu skema komisi aktif (open-ended interval dengan `valid_to IS NULL`).
  - Setiap tenant hanya boleh memiliki maksimal satu rekening bank utama (`is_primary = true`).
  - Kode tenant unik per kantin (`UNIQUE(canteen_id, code)`).
  - Slug tenant unik global.
- **Implementasi**:
  - Generated virtual columns `active_lock` dan `primary_lock` pada skema migrasi MariaDB/MySQL/SQLite dengan indeks unik komposit:
    - `active_lock = (valid_to IS NULL) ? 1 : NULL` -> `UNIQUE(tenant_id, active_lock)`
    - `primary_lock = (is_primary = 1) ? 1 : NULL` -> `UNIQUE(tenant_id, primary_lock)`
- **Verifikasi Uji Otomasi**:
  - `AdminManagementTest::test_two_open_commission_schemes_rejected_by_db_guard`:
    - Dua insert bersamaan dengan `valid_to = NULL` melempar `QueryException` (constraint violation).
  - `AdminManagementTest::test_only_one_primary_bank_account_per_tenant`:
    - Menjadikan rekening kedua sebagai primary otomatis mencabut status primary dari rekening pertama.
