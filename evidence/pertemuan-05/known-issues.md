# Known Issues, Formula Komisi, & Exit Ticket — Modul 5

## 1. Formula Perhitungan & Skema Komisi (Effective-Dating)

### A. Formula Komisi
Komisi dihitung menggunakan formula:
$$\text{Nilai Komisi} = \text{round}(\text{Subtotal Tenant} \times \text{Rate Snapshot})$$
- Hasil pembulatan menggunakan pembulatan standar ke bilangan bulat Rupiah terdekat (`round($subtotal * $rate)`).
- `rate` disimpan dalam bentuk desimal dengan presisi 4 angka di belakang koma (misal: 10% disimpan sebagai `0.1000`, 12.5% sebagai `0.1250`).
- Skema komisi 0% diperbolehkan (`0.0000`).

### B. Aturan Interval Efektif (Half-Open Interval)
Penentuan tarif komisi yang berlaku pada saat transaksi $T$ didasarkan pada interval setengah terbuka:
$$[valid\_from, valid\_to)$$
- Berlaku mulai: $valid\_from \le T$
- Berakhir tepat sebelum: $valid\_to > T$ atau $valid\_to \text{ IS NULL}$
- Jika skema baru aktif pada $T$, skema sebelumnya ditutup tepat pada $T$ (`valid_to = T`). Pada momen persis $T$, skema baru yang berlaku, sedangkan skema lama tidak lagi berlaku (upper-bound exclusive).
- Aturan anti-overlap: Dua interval $[A_{from}, A_{to})$ dan $[B_{from}, B_{to})$ bertumpang tindih jika dan hanya jika $A_{from} < B_{to}$ dan $A_{to} > B_{from}$. Sistem menolak segala bentuk overlapping interval via service logic dan DB virtual guard (`active_lock`).

---

## 2. Jawaban Exit Ticket Praktikum

### Pertanyaan 1: Mengapa tarif komisi harus berversi?
**Jawaban**:
Tarif komisi harus berversi (*effective-dated / versioned*) agar perubahan kebijakan tarif komisi di masa kini atau masa depan tidak merusak atau mengubah secara retroaktif nilai bagi hasil pesanan yang telah terjadi di masa lalu. Dengan pendekatan berversi:
1. Setiap periode waktu memiliki rekam jejak tarif resmi yang jelas dan dapat diaudit.
2. Pengelola kantin dapat menjadwalkan tarif komisi baru untuk tanggal efektif di masa depan tanpa mengganggu tarif yang sedang aktif saat ini.
3. Transparansi dan akuntabilitas antara pengelola kantin dan mitra tenant terjaga secara historis.

### Pertanyaan 2: Bagaimana order lama tetap konsisten ketika tarif komisi berubah?
**Jawaban**:
Order lama tetap konsisten melalui dua mekanisme:
1. **Pola Snapshot Data**: Ketika pesanan terbentuk (`tenant_orders`), sistem menyimpan salinan permanen (*snapshot*) dari tarif komisi (`commission_rate`) dan nominal potongan komisi (`commission_amount`) yang dihitung saat order dibuat.
2. **Kemandirian Riwayat**: Laporan keuangan, invoice, atau mutasi saldo historis selalu mengacu pada nilai snapshot pada baris transaksi tersebut, bukan menghitung ulang secara dinamis menggunakan tabel referensi `commission_schemes`. Sehingga perubahan tarif komisi aktif tidak akan pernah mengubah nilai nominal pesanan masa lampau.

### Pertanyaan 3: Data rekening apa yang perlu dimasking?
**Jawaban**:
1. **Nomor Rekening Mentah (*Account Number*)**: Wajib disamarkan/dimasking pada antarmuka pengguna (UI), API response publik, dan catatan audit trail. Hanya 4 digit terakhir (`account_last4`) yang ditampilkan untuk tujuan konfirmasi visual (misal: `BCA ••6721`).
2. **Plaintext Nomor Rekening pada Database**: Nomor rekening utuh tidak boleh disimpan sebagai teks terbuka di kolom basis data; wajib dienkripsi secara asimetris/simetris menggunakan cipher standar aplikasi (`account_number_cipher` via Laravel `encrypted` cast).

---

## 3. Catatan Teknis & Penanganan Kendala (Troubleshooting Log)
1. **Aksesibilitas Komponen Status Badge**: Komponen `<x-status-badge>` disempurnakan agar mendukung injeksi konten slot teks (`$slot`), sehingga teks berbahasa Indonesia seperti `AKTIF` dan `NONAKTIF` dapat terender dengan kelas warna kontras yang sesuai.
2. **Otorisasi Multi-Tenant Context**: Middleware `SetTenantContext` disesuaikan untuk mengenali relasi peran banyak-ke-banyak (`user_tenant_roles`) selain relasi tradisional kepemilikan tunggal (`tenant->user_id`).
3. **Penyelarasan Skema Relasional**: Seluruh Factory (`CanteenFactory`, `TenantFactory`, dll.) diselaraskan secara presisi dengan skema migrasi MariaDB/SQLite untuk mencegah adanya kolom tidak terdefinisi (`unknown column: slug` pada kantin).
