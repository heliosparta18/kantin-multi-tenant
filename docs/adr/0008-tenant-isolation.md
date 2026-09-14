# 8. Isolasi Multi-Tenant: Context, Scope, Policy, dan Scoped Binding

Date: 2026-09-14

## Status

Accepted

## Konteks

Pada Modul 3, aplikasi telah menerapkan foreign key komposit dan unique constraint pada tingkat database untuk mencegah relasi lintas tenant (cross-tenant integrity). Namun, integritas database saja belum cukup: query aplikasi biasa dapat membaca atau memanipulasi data tenant lain jika pengembang lupa menyertakan klausa `where tenant_id = ...`.

Aplikasi Kantin Multi-Tenant menggunakan arsitektur **shared database, shared schema**, di mana seluruh tenant berbagi tabel database yang sama. Untuk memastikan keamanan data, privasi, dan kepatuhan terhadap **FR-TEN-01**, **Matriks Konteks Akses Lampiran B**, serta **NFR Keamanan dan Privasi**, sistem memerlukan arsitektur **pertahanan berlapis (defense-in-depth)** yang fail-closed (menolak secara default bila ragu/tidak ada konteks).

## Keputusan Arsitektur

Kami menerapkan empat lapis isolasi tenant yang saling melengkapi:

### 1. Siklus Hidup TenantContext (Request-Scoped)
- `TenantContext` didaftarkan sebagai `scoped` pada container Laravel (`$this->app->scoped(TenantContext::class)`), bukan `singleton`.
- Instance dibuat segar untuk setiap HTTP request atau Queue Job, dan dibersihkan (`clear()`) pada blok `finally`. Hal ini mencegah kebocoran state tenant pada worker panjang (long-running worker) atau server daemon.
- Fail closed: jika `TenantContext` belum terisi, operasi query/tulis domain tenant-owned tidak diizinkan mengakses atau membuat data sembarangan.

### 2. Middleware Resolver & Role Check (`SetTenantContext`)
- Resolver berjalan **setelah** autentikasi (`auth`) dan pengecekan role (`role:tenant`), namun **sebelum** route model binding resource child.
- Resolver membaca parameter route `{tenant:slug}` (atau `{tenant}`).
- Resolver memverifikasi:
  1. Tenant berstatus `active` (menolak dengan HTTP 403 jika status suspended/inactive).
  2. Pengguna terautentikasi memiliki kepemilikan/role pada tenant tersebut (`$tenant->user_id === $request->user()->id`), atau admin platform terotorisasi (menolak dengan HTTP 403 jika tidak berhak).
- Mengisi `TenantContext` sebelum memanggil `$next($request)` dan membersihkannya pada blok `finally`.

### 3. Global Scope & Model Integration (`BelongsToTenant`)
- Trait `BelongsToTenant` dipasang pada semua model tenant-owned.
- **Saat membaca**: Menambahkan global scope `where tenant_id = ...` secara otomatis bila context tenant aktif.
- **Saat menulis**: Event `creating` mengisi `tenant_id` secara otomatis dari context. Jika context kosong dan `tenant_id` tidak disediakan, operasi digagalkan (`RuntimeException`).
- Mencegah manipulasi mass assignment dengan mengabaikan/menjaga atribut `tenant_id` (`mergeGuarded(['tenant_id'])`).

### 4. Scoped Route Binding & Authorization Policies
- Mengaktifkan `scopeBindings()` pada grup route bersarang tenant (misal: `/tenant/{tenant:slug}/menus/{menu}`).
- Scoped route binding memastikan item child (menu/order/withdrawal) benar-benar milik parent tenant; bila ada ketidakcocokan, Laravel mengembalikan HTTP 404.
- Kebijakan otorisasi (`MenuPolicy`, `TenantOrderPolicy`, `WithdrawalPolicy`) memvalidasi hak aksi pengguna terhadap resource (HTTP 403 jika terlarang).

### 5. Aturan Bypass Scope Terkontrol (`withoutGlobalScopes`)
- `withoutGlobalScope('tenant')` dilarang digunakan sembarangan di controller atau model query langsung.
- Penggunaan bypass scope hanya sah pada class layanan terpusat:
  - **Katalog Publik**: `App\Modules\Catalog\Services\PublicCatalogQuery`, di mana scope tenant dilepas dan digantikan dengan filter wajib kantin (`canteen_id`) dan status tenant (`status = 'active'`).
  - **Audit/Pelaporan Admin Platform**: Akses lintas tenant wajib mencatat log audit (siapa aktor, tindakan, dan lingkup data).

## Matriks Klasifikasi Tabel & Model

| Kategori | Model / Tabel | Pengamanan yang Berlaku |
|---|---|---|
| **Platform-Scoped** | `User` (`users`), `Canteen` (`canteens`), `Table` (`tables`), `TableSession` (`table_sessions`) | Otentikasi, Role platform admin/canteen, validasi QR kantin |
| **Tenant-Owned** | `Tenant` (`tenants`), `Balance` (`balances`), `Category` (`categories`), `Menu` (`menus`), `Modifier` (`modifiers`), `MenuStock` (`menu_stocks`), `Commission` (`commissions`), `Order` / `TenantOrder` (`orders`), `OrderItem` (`order_items`), `Transaction` (`transactions`), `Withdrawal` (`withdrawals`) | `TenantContext`, `BelongsToTenant` global scope, Scoped route binding, Policies, Composite FK |

## Konsekuensi

- **Positif**:
  - Isolasi data tenant terjamin di semua lapisan (URL, routing, policy, ORM query, dan database).
  - Mengurangi risiko kebocoran data (data leakage) akibat kelalaian pengembang dalam menulis klausa `where`.
  - Penolakan akses terjadi seawal mungkin (fail-closed) dengan HTTP 403 atau 404.
  - Job queue aman dari kebocoran konteks antar proses.
- **Negatif / Perhatian Tambahan**:
  - Query publik lintas tenant (seperti katalog pelanggan kantin) harus melalui query service khusus yang telah diaudit.
  - Unit/feature test harus mengelola setup context atau menggunakan helper yang sesuai.
