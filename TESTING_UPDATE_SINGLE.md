# Arbitrary Table Write via getUpdateSingle — Testing Guide

## Overview

`CBController::getUpdateSingle()` used to accept `table`, `column`, `value`, and `id` from the query string and run:

```sql
UPDATE {table} SET {column} = {value} WHERE id = {id}
```

Any logged-in admin could craft a URL on **any module** and write to **any table/column** (e.g. change `cms_users.id_cms_privileges` or `password`).

The fix restricts updates to:

- the **current module table only** (`$this->table`)
- **allowlisted columns** (`upload` / `filemanager` form fields only)
- plus a **denylist** for sensitive columns (password, privilege ids, etc.)

Toggle via `.env`:

```env
# true  = hardened (default, use in production)
CB_UPDATE_SINGLE_HARDENING_ENABLED=true

# false = legacy vulnerable behavior (local testing only)
CB_UPDATE_SINGLE_HARDENING_ENABLED=false
```

After changing `.env`, clear config cache:

```bash
php artisan config:clear
```

---

## Quick start

```bash
php tests/Manual/test_update_single_manual.php
```

With your local URL and test user id:

```bash
php tests/Manual/test_update_single_manual.php --base-url=https://merchandiser-backup.test --victim-id=2
```

Or via Artisan (if your PHP version supports this Laravel app):

```bash
php artisan security:test-update-single
```

---

## Method 1: Browser test (recommended)

### Prerequisites

- App running locally (e.g. `https://merchandiser-backup.test`)
- Logged in as an **admin** user (any module — exploit works from any module route)
- A non-superadmin test user to target (note their `id` and current `id_cms_privileges`)

### Step 1 — Record baseline

In MySQL / TablePlus / `php artisan tinker`:

```sql
SELECT id, email, name, id_cms_privileges
FROM cms_users
WHERE id = 2;   -- replace 2 with your test user id
```

Example before:

| id | email | id_cms_privileges |
|----|-------|-------------------|
| 2 | staff@example.com | 3 |

### Step 2 — Run the privilege-escalation attack URL

While still logged in as admin, open this URL in the browser (adjust host, user id, and privilege id):

```
https://YOUR-SITE/admin/users/update-single?table=cms_users&column=id_cms_privileges&value=1&id=2
```

You can use **any module path**, not only `users`. Example from another menu:

```
https://YOUR-SITE/admin/trx_posts/update-single?table=cms_users&column=id_cms_privileges&value=1&id=2
```

### Step 3 — Expected results

| `CB_UPDATE_SINGLE_HARDENING_ENABLED` | What happens |
|--------------------------------------|----------------|
| `false` (legacy) | Redirect back with success message; `id_cms_privileges` changes to `1` in DB |
| `true` (hardened) | **HTTP 403** — `Column is not allowed` (or similar error page) |

### Step 4 — Verify database unchanged

```sql
SELECT id, email, id_cms_privileges FROM cms_users WHERE id = 2;
```

`id_cms_privileges` must still be `3` (or whatever it was in Step 1).

---

## Method 2: Additional attack URLs

Replace `YOUR-SITE`, `admin`, and ids as needed.

### Password overwrite

```
https://YOUR-SITE/admin/users/update-single?table=cms_users&column=password&value=hacked&id=2
```

| Flag | Result |
|------|--------|
| `false` (legacy) | Sets `password` to literal `hacked` for user 2 |
| `true` (hardened) | **403** blocked |

### Write to a different table entirely

```
https://YOUR-SITE/admin/users/update-single?table=cms_privileges&column=name&value=Pwned&id=1
```

| Flag | Result |
|------|--------|
| `false` (legacy) | Updates `cms_privileges.name` |
| `true` (hardened) | **403** — `Table is not allowed` |

### Arbitrary column on current module table

```
https://YOUR-SITE/admin/users/update-single?table=cms_users&column=email&value=attacker@evil.com&id=2
```

| Flag | Result |
|------|--------|
| `false` (legacy) | Changes user email |
| `true` (hardened) | **403** — column not allowlisted |

---

## Method 3: curl (with session cookie)

1. Log in via browser.
2. Copy the `laravel_session` (or app session) cookie from DevTools.
3. Run:

```bash
curl -i -b "laravel_session=YOUR_SESSION_COOKIE" \
  "https://YOUR-SITE/admin/users/update-single?table=cms_users&column=id_cms_privileges&value=1&id=2"
```

**With `CB_UPDATE_SINGLE_HARDENING_ENABLED=true`:** expect `HTTP/1.1 403 Forbidden` in the response headers.

---

## Toggle vulnerable vs hardened behavior (dev only)

Set in `.env`:

```env
CB_UPDATE_SINGLE_HARDENING_ENABLED=false
```

Then:

```bash
php artisan config:clear
```

1. Run the attack URL → privilege **will** change (legacy behavior).
2. Set `CB_UPDATE_SINGLE_HARDENING_ENABLED=true` and run `php artisan config:clear`.
3. Run the same URL → **403**, DB unchanged.

**Never set `false` in production.**

---

## Test Files

1. **Manual script**: `tests/Manual/test_update_single_manual.php` (run with plain `php`, no artisan needed)
2. **Artisan command**: `app/Console/Commands/TestUpdateSingleSecurity.php`
3. **Unit tests**: `tests/Feature/UpdateSingleSecurityTest.php`
4. **This guide**: `TESTING_UPDATE_SINGLE.md`

---

## Checklist

- [ ] Attack URL returns **403** after fix
- [ ] `cms_users.id_cms_privileges` unchanged after attack
- [ ] `cms_users.password` unchanged after password attack URL
- [ ] `cms_privileges` table unchanged after cross-table attack URL
- [ ] `php artisan security:test-update-single` logic checks all **PASS**
