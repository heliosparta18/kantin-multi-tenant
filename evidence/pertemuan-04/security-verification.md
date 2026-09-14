# Evidence Pertemuan 04 — Isolasi Multi-Tenant

**Fase:** Fase 1 — Keamanan Data  
**Ketertelusuran:** FR-TEN-01, Matriks Konteks Akses Lampiran B, NFR Keamanan dan Privasi  
**Output:** TenantContext request-scoped, middleware resolver, BelongsToTenant global scope, scoped route binding, authorization policies, bypass terkontrol, dan security regression test.

---

## Matriks Allow / Deny (12 Skenario Pengujian Isolasi Tenant)

| No | Skenario Pengujian | Aktor / Konteks | Aksi / Endpoint | Target Resource | Ekspektasi | Status |
|:--:|---|---|---|---|:---:|:---:|
| 1 | Akses menu milik sendiri | Tenant A | `GET /tenant/{tenantA}/menus` | Menu Tenant A | **ALLOW (200 OK)** | Passed |
| 2 | Akses detail menu milik tenant lain | Tenant A | `GET /tenant/{tenantA}/menus/{menuB}` | Menu Tenant B | **DENY (404 / 403)** | Passed |
| 3 | Update menu milik tenant lain | Tenant A | `PUT /tenant/{tenantA}/menus/{menuB}` | Menu Tenant B | **DENY (404 / 403)** Data B tidak berubah | Passed |
| 4 | Hapus menu milik tenant lain | Tenant A | `DELETE /tenant/{tenantA}/menus/{menuB}` | Menu Tenant B | **DENY (404 / 403)** Record B utuh | Passed |
| 5 | Scoped route binding mismatch | Tenant A | `GET /tenant/{tenantA}/menus/{menuB}` | Menu Tenant B | **DENY (404 Not Found)** | Passed |
| 6 | Akses pesanan milik tenant lain | Tenant A | `GET /tenant/{tenantA}/orders/{orderB}` | Order Tenant B | **DENY (404 / 403)** | Passed |
| 7 | Akses pencairan (withdrawal) tenant lain | Tenant A | `GET /tenant/{tenantA}/withdrawals/{withdrawalB}` | Withdrawal Tenant B | **DENY (404 / 403)** | Passed |
| 8 | Akses route internal oleh tenant nonaktif / suspended | Tenant Nonaktif | `GET /tenant/{tenantSuspended}/dashboard` | Tenant Dashboard | **DENY (403 Forbidden)** | Passed |
| 9 | Akses route tenant oleh user tanpa role/membership di tenant tersebut | User Asing / Customer | `GET /tenant/{tenantA}/dashboard` | Tenant Dashboard | **DENY (403 Forbidden)** | Passed |
| 10 | Upaya manipulasi `tenant_id` via mass assignment | Tenant A | `POST /tenant/{tenantA}/menus` (`payload: tenant_id=B`) | Menu Tenant A | **OVERWRITTEN / GUARDED** Tersimpan dengan `tenant_id` A | Passed |
| 11 | Query katalog publik kantin via QR token | Customer Publik | `PublicCatalogQuery::getMenusForCanteen(canteenA)` | Menu Kantin A | **ALLOW (200 OK)** Hanya tenant aktif Kantin A, 0 kebocoran kantin lain | Passed |
| 12 | Isolasi konteks job queue berurutan | Queue Worker | Sequential Job A lalu Job B | Context Tenant | **ISOLATED** Job A tereksekusi di context A, Job B di context B, context bersih setelahnya | Passed |

---

## Exit Ticket

### 1. Mengapa global scope saja belum cukup?
**Jawaban:**  
Global scope hanya bekerja pada level query Eloquent untuk menambahkan klausa `where tenant_id = ...`. Global scope belum melindungi aplikasi dari:
- Resolusi URL atau route binding yang salah (misalnya child model dimuat tanpa memvalidasi parent-nya).
- Otorisasi aksi pengguna (apakah aktor saat ini berhak mengedit, menghapus, atau menyetujui transaksi tersebut).
- Penulisan data baru (mass assignment atau penambahan data tanpa `tenant_id`).
- Jalur query yang sengaja atau tidak sengaja mem-bypass scope (seperti raw query atau query builder).
Oleh karena itu, diperlukan pertahanan berlapis: request-scoped `TenantContext`, resolver middleware, scoped route binding, policy, auto-fill saat write, serta database composite foreign keys.

### 2. Kapan bypass scope sah?
**Jawaban:**  
Bypass scope (`withoutGlobalScope('tenant')`) hanya sah pada alur bisnis yang secara eksplisit bersifat lintas tenant (cross-tenant) yang telah terotorisasi, seperti:
1. Katalog publik kantin (di mana pembeli melihat menu dari semua stan/tenant yang aktif dalam satu kantin tertentu).
2. Laporan konsolidasi atau audit oleh Super Admin / Pengelola Kantin.
Bypass scope **wajib** dipusatkan pada kelas service khusus (misalnya `PublicCatalogQuery`), segera menggantikan scope yang dilepas dengan filter pengganti yang ketat (misalnya filter `canteen_id` dan `status = 'active'`), serta dicatat ke dalam log audit bila dilakukan oleh admin.

### 3. Bagaimana mencegah context bocor pada queue worker?
**Jawaban:**  
Queue worker di Laravel adalah long-running process yang mengeksekusi banyak job secara bergantian pada satu proses PHP yang sama. Untuk mencegah kebocoran context:
1. Daftarkan `TenantContext` sebagai `scoped` di service provider, bukan `singleton`.
2. Job tidak boleh menerima objek `TenantContext` atau instance model penuh melalui constructor, melainkan hanya menyimpan data primitif minimum seperti `tenant_id` dan ID target resource.
3. Di dalam method `handle()`, job memuat tenant dari database, memvalidasi status keaktifannya, lalu membentuk `TenantContext`.
4. Selalu bersihkan context (`$tenantContext->clear()`) di dalam blok `finally` agar jika job selesai ataupun mengalami exception/kegagalan, state tenant tidak tertinggal untuk job berikutnya.
