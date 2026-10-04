# SOP Onboarding Tenant & Demo Script (Pertemuan 05)

## 1. Ikhtisar Alur Onboarding (UC-21)
Proses onboarding tenant dilakukan oleh Pengelola Kantin (Canteen Manager) melalui portal administratif terpadu. Seluruh proses bersifat atomik dalam transaksi database tunggal untuk menjamin konsistensi data.

## 2. Langkah-Langkah Operasional

### Langkah 1: Autentikasi Pengelola Kantin
1. Buka peramban ke halaman login: `http://localhost:8000/login`.
2. Masukkan kredensial pengelola:
   - **Email**: `admin@kantin.test`
   - **Password**: `password`
3. Klik tombol **Masuk**.
4. Sistem memverifikasi kredensial dan peran pengelola (`UserCanteenRole`), kemudian mengarahkan aktor secara otomatis ke dasbor admin (`/admin/dashboard` atau `/admin/tenants`).

### Langkah 2: Akses Manajemen Tenant
1. Dari sidebar navigasi atau URL, buka menu **Tenant & Skema Komisi** di `/admin/tenants`.
2. Halaman menampilkan:
   - Nama kantin yang dikelola (misal: *Kantin Terpadu Poliwangi*).
   - Daftar stan/tenant aktif & nonaktif beserta penanggung jawab, rekening utama (tersamar 4 digit), tarif komisi aktif, dan status badge.
   - Tombol **+ Daftarkan tenant**.

### Langkah 3: Formulir Pendaftaran Tenant
1. Klik tombol **+ Daftarkan tenant** (mengarahkan ke `/admin/tenants/create`).
2. Isi data tenant pada formulir:
   - **Nama Tampilan Tenant**: misal `Warung Padang Raso Minang`
   - **Kode Tenant**: misal `PADANG01` (unik per kantin)
   - **Slug URL**: misal `warung-padang` (unik global)
   - **Tarif Komisi Awal (%)**: misal `12.5`
   - **Nama Penanggung Jawab (PIC)**: misal `Umar Bakri`
   - **Surel PIC**: misal `umar.padang@kantin.test`
   - **Bank Rekening**: misal `BCA`
   - **Nama Pemilik Rekening**: misal `Umar Bakri`
   - **Nomor Rekening**: misal `5412890011`
3. Klik **Simpan Tenant**.

### Langkah 4: Verifikasi Hasil Atomik
1. Sistem mengeksekusi `CreateTenant` dalam DB transaction:
   - Record tenant dibuat dengan status `active`.
   - Record saldo tenant (`tenant_balances`) dibuat dengan nilai awal 0 (`available_amount` = 0, `held_amount` = 0).
   - Record skema komisi aktif (`commission_schemes`) dibuat dengan `valid_from = now()` dan `valid_to = null`.
   - Record akun penanggung jawab (`users`) dibuat dengan status `active` dan `role = tenant`.
   - Hubungan peran tenant (`user_tenant_roles`) dibuat dengan `role = owner`.
   - Record rekening bank (`tenant_bank_accounts`) disimpan dengan enkripsi AES-256-CBC dan `account_last4 = '0011'`.
   - Record audit trail (`audit_logs`) tercatat untuk pembuatan tenant dan komisi (nomor rekening mentah tidak pernah dicatat).
   - Tautan atur kata sandi (password reset token) dikirimkan via email (logged di `storage/logs/laravel.log`) setelah DB commit berhasil.
2. Pengguna dialihkan kembali ke `/admin/tenants` dengan pesan sukses.

### Langkah 5: Penjadwalan & Perubahan Komisi (Effective-Dating)
1. Pada baris tenant terkait di `/admin/tenants`, klik tautan **Ubah komisi** (`/admin/tenants/{tenant}`).
2. Halaman menampilkan tarif aktif saat ini dan riwayat versi komisi sebelumnya (dilengkapi nama pengelola pengubah via audit trail).
3. Masukkan tarif komisi baru dan tanggal mulai berlaku (`valid_from` masa mendatang).
4. Klik **Simpan Jadwal Komisi**:
   - Skema baru dijadwalkan tanpa tumpang tindih (`lockForUpdate` anti-race condition).
   - Skema lama otomatis ditutup dengan `valid_to` yang berimpit pada waktu efektif baru.

### Langkah 6: Pengelolaan Status Tenant (Deaktivasi / Reaktivasi)
1. Untuk menonaktifkan tenant, klik tombol **Nonaktifkan** pada baris tenant.
2. Status berubah menjadi `inactive` (badge `NONAKTIF`).
3. Tenant dan menunya otomatis disembunyikan dari katalog publik pelanggan.
4. Seluruh data historis transaksi, pesanan, dan saldo tetap aman (tidak pernah dilakukan `DELETE` fisik).
5. Klik **Aktifkan** untuk mengembalikan tenant ke status `active`.
