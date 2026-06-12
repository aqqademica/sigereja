# SIGereja - Production Readiness: Issues & Solutions Reference

This document serves as a historical reference detailing the critical issues encountered during the final production readiness phase of the SIGereja project and exactly how they were resolved. This guide can be used to troubleshoot similar issues in the future or to onboard new developers onto the project's architectural decisions.

---

## 1. CSRF Token Mismatch & Unpredictable Session Loss
**The Issue:** 
Users experienced random logouts and "CSRF Token Mismatch" errors when submitting forms. This occurred because PHP sessions were not persisting properly. The root cause was scattered, inconsistent `session_start()` calls across various files, and the lack of explicit, secure session cookie parameters. Without strict cookie parameters, some browsers rejected the session cookie.

**The Solution:**
* **Centralization:** Created `includes/session.php`. This file forces specific, secure session behaviors globally:
  * Uses `session_set_cookie_params()` to enforce `HttpOnly` (mitigates XSS), `SameSite=Lax` (mitigates CSRF), and optionally `Secure` (if HTTPS is detected).
  * Automatically handles session initialization securely.
* **Refactoring:** Replaced all scattered `session_start()` instances in `actions/auth.php`, `actions/logout.php`, `includes/header.php`, `includes/admin_header.php`, `includes/csrf.php`, and various page scripts with `require_once __DIR__ . '/session.php'`.
* **Fixation Protection:** Added `session_regenerate_id(true)` upon successful user login in `actions/auth.php` to prevent session fixation attacks.
* **Safe Teardown:** Updated `actions/logout.php` to proactively trigger `setcookie()` with an expiration in the past to fully destroy the session cookie on the client-side, alongside `session_destroy()`.

---

## 2. Broken PWA Manifest & Service Worker Paths (404 Not Found)
**The Issue:**
The Progressive Web App (PWA) configuration relied on hardcoded relative paths (e.g., `href="../../manifest.json"` or `/SIGereja/sw.js`). When accessing the app from different directory depths or setting up a different Virtual Host (like `http://sigereja.local`), these hardcoded paths triggered 404 errors, breaking PWA installability and caching.

**The Solution:**
* **Dynamic Base URL:** Added logic in `config/database.php` to define a global `BASE_URL` constant. It pulls from the environment variable `$_ENV['APP_URL']` and falls back gracefully.
* **Path Standardization:** Updated `includes/header.php`, `pages/jemaat/jadwal.php`, and `pages/jemaat/pendaftaran.php` to prefix asset paths dynamically: `href="<?= BASE_URL ?>/manifest.json"`.

---

## 3. Missing Sidebar/Topbar in Admin Panel (Include Pathing Error)
**The Issue:**
`includes/admin_header.php` used relative includes like `include 'admin_sidebar.php';`. When `admin_header.php` was included by a script residing in a nested directory (e.g., `pages/admin/users.php`), the relative path resolved incorrectly relative to the *calling script*, not relative to the header file itself, causing missing UI components.

**The Solution:**
* **Absolute Referencing:** Changed the include statements to use the PHP `__DIR__` magic constant (`include __DIR__ . '/admin_sidebar.php';`). This ensures that the inclusion is strictly evaluated relative to the folder where `admin_header.php` physically resides.

---

## 4. Silent Failures on Sidi (Confirmation) Registration
**The Issue:**
When submitting Sidi registrations, the database occasionally rejected the inserts silently (or threw PDO exceptions if error reporting was high). This happened because the database schema for `tblPendaftaranSidi` strictly requires a foreign key mapping for `id_keluarga`, but the UI forms could theoretically submit without it if the user's account lacked a tied family record.

**The Solution:**
* **Pre-Insert Validation:** Added explicit server-side checks in both `pages/admin/pendaftaran.php` and `pages/jemaat/pendaftaran.php`. The application now verifies that `if (!$id_jemaat || !$id_keluarga)` the system rejects the submission with a clear, user-friendly error message (`"Keluarga dan Jemaat wajib dipilih."`) before attempting the SQL `INSERT`.

---

## 5. Incomplete Data Logging on Mutasi (Transfer) Approvals
**The Issue:**
When administrators approved a "Mutasi" (Church Transfer), the system successfully updated the member's status to `Pindah Gereja (Mutasi)`. However, the destination church (`gereja_tujuan`) and the reason for leaving (`alasan_mutasi`) were trapped in the Mutasi history table and were not easily accessible on the main Jemaat (Member) profile.

**The Solution:**
* **Contextual Data Propagation:** Updated the approval logic in `pages/admin/mutasi.php`. Before updating the member's status, the script now actively fetches the destination and reason from `tblMutasi`, concatenates them into a readable string (`Pindah ke [Tujuan] - Alasan: [Alasan]`), and explicitly writes this into the `alasan_pindah` column of `tblJemaat` during the approval `UPDATE` execution.

---

## 6. Password Reset Token Accumulation (Database Bloat)
**The Issue:**
Password reset tokens have a strict expiry timestamp (`reset_token_expiry`). However, if a user requested a reset but never clicked the link, that expired token and timestamp sat in the `users` database table indefinitely.

**The Solution:**
* **Self-Cleaning Script:** Added an automated garbage collection query at the top of `actions/reset_password.php`. Every time the password reset system is interacted with, it runs: 
  `UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE reset_token_expiry < NOW()`
  This efficiently purges expired tokens globally without needing a CRON job.

---

## 7. Client-Side Authentication Validation Bypass
**The Issue:**
The application required passwords to be at least 8 characters long, but this was only enforced using the HTML5 `minlength="8"` attribute in the frontend forms. A malicious actor could easily remove this attribute via browser dev tools and submit a weak 1-character password.

**The Solution:**
* **Server-Side Enforcement:** Added a strict backend validation block in `actions/auth.php`. Regardless of what the frontend submits, PHP now enforces `if (strlen($password) < 8)` and throws an error back to the UI, guaranteeing database integrity and account security.
