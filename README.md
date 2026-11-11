# Basilgo Compound – House and Room Rentals (PHP + Tailwind CSS)

Same folder pattern as before: `auth/`, `config/`, `includes/`, `models/`, `assets/`, and one folder per module
(`tenants/`, `rooms/`, `billing/`, `invoices/`, `payments/`, `approvals/`, `rates/`).

## Run
```
cd basilgo-php
php -S localhost:8000 router.php
```
Open http://localhost:8000 (PHP 8.1+; internet is needed for the Tailwind CDN and the Google font). The `data/` folder must be writable.
`router.php` only matters for PHP's built-in server: it blocks the browser from reading `data/db.json` (which contains password hashes). On Apache, `data/.htaccess` does that job.

## Demo accounts
| Role | Username | Password |
|---|---|---|
| Administrator | admin | admin123 |
| Staff | staff | staff123 |
| Tenant (metered water) | juan | tenant123 |
| Tenant (free water) | ana | tenant123 |

## Cash payments & approval (no GCash / online payments)
1. A tenant hands cash to **staff**.
2. Staff open **Record cash payment**, pick the tenant, type the amount received (and the date received).
   Nothing changes yet — it becomes a **pending request**.
3. The **admin** sees it under **Approvals** (balance before → after is shown) and approves or rejects it, with an optional reason.
4. Only on **approve** is the amount deducted from the tenant's outstanding balance and added to the payment history (with who recorded it and who approved it).

Safeguards: cash only; the amount can't exceed what the tenant still owes (counting cash already waiting for approval, so the same money is never deducted twice);
staff can cancel their own pending request; a payment is re-checked against the live balance at approval time; forms lock after the first click to prevent double submits.
Admin entries are applied immediately (the admin is the approver).

## Other rules
- Electricity ₱16/kWh and water ₱35/m³ (estimate for Cebu City — verify with MCWD). Admin edits both under **Rates**.
- Tenants on the **Free water** plan are never billed for water.
- Staff edits to tenant information also become *requests* that the admin approves.
- Tenants only see their own balance, invoices and payments.

## UI notes
- Tailwind (CDN). Theme + reusable component classes (`btn`, `card`, `badge-*`, `alert-*`, `input`, …) are defined once in `includes/head.php`; `assets/css/app.css` holds only keyframes, table-to-card stacking on phones, and print styles.
- Colour meaning is fixed: **green** = paid / approved / available · **amber** = pending / needs attention · **rose** = money owed / rejected · **sky** = information / occupied / metered · **slate** = neutral.
- Responsive: sidebar becomes a drawer below 1024px; tables become cards below 640px. Animations respect *reduce motion*.

## Structure
```
includes/  head.php (Tailwind theme + components) header.php footer.php auth_check.php helpers.php sample_data.php
models/    database_access.php   <- data layer (JSON file now; replace bodies with PDO later, keep method names)
auth/      login.php logout.php
assets/    css/app.css  js/app.js
router.php dev-server guard
```
Data is stored in `data/db.json` (shared by all users, created on first run). **Delete it once after updating** so the new demo data (cash-only history and a sample pending payment) is created; or use *Utility rates → Reset demo data*.
Never commit real database passwords — use environment variables when you connect a real database.
