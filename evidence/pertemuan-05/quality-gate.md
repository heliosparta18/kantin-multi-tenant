# Quality Gate Verification — Modul 5

Laporan pemenuhan standar kualitas kode (code style, static checks, unit/feature tests, dan front-end build).

---

## 1. Ringkasan Eksekusi Quality Gate

| Pemeriksaan | Perintah | Status | Keterangan |
|---|---|---|---|
| **PHPUnit / Pest Test** | `php artisan test` | **PASSED** | 74 passed, 1 skipped, 1 risky, 0 failures (206 assertions) |
| **Pertemuan 05 Suite** | `php artisan test tests/Feature/AdminManagementTest.php ...` | **PASSED** | 28 passed, 0 failures (111 assertions) |
| **Laravel Pint (Style)** | `vendor\bin\pint --test` | **PASSED** | Seluruh berkas PHP memenuhi standar PSR-12 / Laravel Pint |
| **Front-end Build (Vite)** | `npm run build` | **PASSED** | Aset CSS & JS terkompilasi optimal (Vite v8.2.2) |

---

## 2. Rincian Uji Fitur Pertemuan 05

### A. AdminManagementTest (9 Pengujian)
1. `test_only_canteen_manager_can_see_tenant_index` -> **PASSED**
2. `test_creating_tenant_also_creates_active_commission_and_balance` -> **PASSED**
3. `test_tenant_code_is_unique_per_canteen` -> **PASSED**
4. `test_commission_schedule_is_effective_dated` -> **PASSED**
5. `test_two_open_commission_schemes_rejected_by_db_guard` -> **PASSED**
6. `test_bank_account_is_stored_encrypted_with_last4` -> **PASSED**
7. `test_only_one_primary_bank_account_per_tenant` -> **PASSED**
8. `test_cannot_remove_last_owner` -> **PASSED**
9. `test_audit_log_is_written_on_sensitive_action` -> **PASSED**

### B. CommissionScheduleBoundaryTest (9 Pengujian)
1. `test_commission_interval_is_half_open_upper_exclusive` -> **PASSED**
2. `test_valid_to_at_instant_t_does_not_apply_at_t` -> **PASSED**
3. `test_overlapping_commission_intervals_are_rejected` -> **PASSED**
4. `test_future_commission_can_be_scheduled_while_current_stays_active` -> **PASSED**
5. `test_snapshot_on_tenant_order_is_immutable_against_commission_change` -> **PASSED**
6. `test_db_guard_rejects_second_open_ended_commission_scheme` -> **PASSED**
7. `test_commission_rate_is_rounded_to_nearest_integer_rupiah` -> **PASSED**
8. `test_zero_percent_commission_rate_is_allowed` -> **PASSED**
9. `test_audit_log_captures_actor_and_both_old_and_new_rates` -> **PASSED**

### C. TenantOnboardingTest (5 Pengujian)
1. `test_onboarding_creates_tenant_owner_bank_commission_and_balance_atomically` -> **PASSED**
2. `test_onboarding_rejects_existing_pic_email_and_missing_bank` -> **PASSED**
3. `test_index_lists_pic_primary_bank_and_active_commission` -> **PASSED**
4. `test_deactivated_tenant_is_hidden_from_catalog_but_history_is_kept` -> **PASSED**
5. `test_manager_of_other_canteen_cannot_change_status_and_status_is_whitelisted` -> **PASSED**
6. `test_commission_history_shows_the_acting_manager_and_rejects_retroactive_dates` -> **PASSED**

### D. LoginLockoutTest (5 Pengujian)
1. `test_five_failed_logins_within_ten_minutes_locks_account_for_fifteen_minutes` -> **PASSED**
2. `test_lockout_persists_across_ip_changes` -> **PASSED**
3. `test_inactive_account_is_rejected_with_generic_message` -> **PASSED**
4. `test_admin_is_redirected_to_admin_dashboard` -> **PASSED**
5. `test_tenant_operator_is_redirected_to_own_tenant_dashboard` -> **PASSED**
6. `test_login_page_is_indonesian_and_states_the_policy` -> **PASSED**
7. `test_session_expires_after_eight_hours_of_inactivity` -> **PASSED**

---

## 3. Log Hasil Pemeriksaan Terakhir
Output eksekusi tersimpan pada:
- `evidence/pertemuan-05/test-results/phpunit-admin.txt`
- `evidence/pertemuan-05/test-results/phpunit-all.txt`
