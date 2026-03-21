# QR Events Launch Plan

## Current Product Status

- Core auth and onboarding are working.
- Event workspaces, media review, collaborator invites, exports, admin plans, admin billing, and cleanup tooling exist.
- Role model is now:
    - normal user: 0-1 owned events
    - business user: 2+ owned events
    - super admin: configured by email

## Active Workstream

### 1. Billing

- [x] Add owner-facing Stripe checkout for unpaid events
- [x] Add Stripe webhook payment confirmation
- [x] Persist Stripe checkout/payment references on events
- [x] Show billing CTA and payment state in event settings
- [x] Add billing tests for checkout + webhook + owner UI
- [x] Patch Composer security advisory (`league/commonmark` 2.8.2)

### 2. Business Experience

- [x] Restrict business dashboard to multi-event owners
- [x] Make business dashboard portfolio-oriented instead of generic SaaS metrics
- [ ] Add explicit “create another event / upgrade to business” UX if needed

### 3. Production Hardening

- [ ] Confirm SMTP mail delivery in production
- [ ] Confirm S3 upload disk and temporary/public URL behavior
- [ ] Run migrations on production database
- [ ] Build frontend assets for production
- [ ] Run queue worker as a managed service
- [ ] Run scheduler every minute
- [ ] Configure FFmpeg and Imagick on the server
- [ ] Set up logs / error monitoring / backups

### 4. Final QA

- [ ] Full signup -> onboarding -> event owner flow
- [ ] Guest upload -> owner review flow
- [ ] Collaborator invite flow
- [ ] Multi-event owner -> business dashboard flow
- [ ] Stripe payment -> webhook -> paid event flow
- [ ] Super admin plan + billing + cleanup flow

## Required Production Environment

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=...`
- database credentials
- mailer credentials
- S3 credentials
- `QUEUE_CONNECTION=database` or Redis
- `STRIPE_KEY`
- `STRIPE_SECRET`
- `STRIPE_WEBHOOK_SECRET`

## Server Runtime Commands

### One-time deploy

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If you change env values after deploy:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Background processes

Queue worker:

```bash
php artisan queue:work --tries=1 --timeout=0
```

After each deploy:

```bash
php artisan queue:restart
```

Scheduler:

```bash
* * * * * php /path/to/project/artisan schedule:run >> /dev/null 2>&1
```

Recommended managed services:

- Supervisor or systemd for `queue:work`
- system cron for `schedule:run`

### Suggested systemd service

```ini
[Unit]
Description=QR Events queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/qrevents/artisan queue:work --tries=1 --timeout=0
WorkingDirectory=/var/www/qrevents

[Install]
WantedBy=multi-user.target
```

## Notes

- Billing is the highest-priority missing launch feature.
- Stripe is now implemented for event owners, so the next step is a production smoke pass, not more dashboard expansion.
