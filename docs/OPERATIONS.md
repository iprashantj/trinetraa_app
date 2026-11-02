# Operations guide

## 1. One-time server setup (cPanel)

1. **Deploy** as usual (`.cpanel.yml`), then run the new migrations once:
   `php artisan migrate --force` (they only add tables and columns).
2. **Cron job** (cPanel → Cron Jobs, every minute). This runs everything scheduled below:
   `* * * * * /opt/cpanel/ea-php84/root/usr/bin/php /home/trinetraaoptyra7/trinetraa_app/artisan schedule:run >/dev/null 2>&1`
3. Make sure `storage/` is writable (backups and uploaded eye-test reports live there).

## 2. What runs automatically

| When | Command | What it does |
| --- | --- | --- |
| 02:30 daily | `db:backup --keep=14` | Gzip dump of the database to `storage/app/backups/`, keeps 14 days |
| 10:00 daily | `reminders:send` | Eye-test follow-ups, birthdays, review requests (consent-aware, never repeats) |
| periodic | `reservations:expire` | Releases store-pickup reservations after 48 h |

`php artisan reminders:send --dry-run` shows what would be sent without sending.

## 3. Backups and restore

- Backups are pure PHP (the host blocks `mysqldump`), so they work on shared hosting.
- Download them from the file manager (`trinetraa_app/storage/app/backups/`) and keep a copy off the server regularly.
- **Restore** into an empty database:
  `gunzip -c trinetraa_next_YYYYMMDD_HHMMSS.sql.gz | mysql -u USER -p DATABASE`
  or in phpMyAdmin: Import → choose the `.sql.gz` file.
- Run one on demand with `php artisan db:backup`.

## 4. Keys to set (all optional; the app falls back to wa.me / sms: / email links without them)

Add to the server `.env`, then `php artisan config:cache`.

| Purpose | Keys |
| --- | --- |
| WhatsApp Cloud API | `WHATSAPP_TOKEN`, `WHATSAPP_PHONE_ID`, optional `WHATSAPP_TEMPLATE`, `WHATSAPP_TEMPLATE_LANG` |
| SMS (MSG91) | `MSG91_AUTH_KEY`, `MSG91_SENDER`, `MSG91_TEMPLATE_ID` (India needs DLT-registered templates) |
| Email | `RESEND_API_KEY` |
| Card / online payment links | `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET` |
| Default Google review link | `GOOGLE_REVIEW_URL` (or set one per branch under Branches) |

WhatsApp free-text messages only deliver inside WhatsApp's 24-hour window; use an approved template for everything else.

## 5. Settings to fill in the admin panel

- **Settings → Payments:** shop UPI ID.
- **Branches:** address, phone, hours, Maps URL, **GSTIN**, **invoice prefix**, **Google review link** (Pune and Mumbai store pages stay hidden from search engines until address/phone are set).
- **Stock Items:** HSN code, GST % and **cost price** (profit reports only count lines with a cost price).

## 6. Invoicing notes

- Invoice numbers look like `NSK/2026-27/0001`, run per branch and financial year (April–March), and are never reused.
  Because gaps matter for GST, prefer returns/refunds over deleting a bill.
- GST is split into CGST/SGST (intra-state). Loyalty points: 1 point per ₹100 paid, 1 point = ₹1, up to 25% of a bill. Points are matched to the customer's website account by phone or email.
