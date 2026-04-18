# QR Events Deploy Checklist

## Default Path

Push `main` and let GitHub Actions handle deploys through [.github/workflows/deploy.yml](/Users/dev/Documents/codex/qrevents/.github/workflows/deploy.yml).

### GitHub Actions Flow

```bash
git push origin main
gh run list --repo alinme/qrevents --workflow deploy --limit 1
gh run watch --repo alinme/qrevents <run-id> --exit-status
```

Verify after the workflow completes:

```bash
curl -I https://eventsmart.app
```

Notes:
- The deploy workflow uses the configured repo secrets and deploy key.
- It installs Composer dependencies only when `composer.json` or `composer.lock` changed.
- It installs npm dependencies only when `package.json` or `package-lock.json` changed.
- It rebuilds frontend assets only when frontend-related files changed.
- It runs migrations only when `database/migrations/` changed.
- It restarts the queue only for queue-related changes.
- `tests` and `linter` are manual-only and are not part of the default production deploy path.

## Manual Fallback

## Server

```bash
ssh eventsmart
cd /home/eventsmart/htdocs/eventsmart.app
```

## Update Code

```bash
git stash push --include-untracked -m "pre-deploy-generated-drift" || true
git pull origin main
git stash pop || true
```

## Install Dependencies

Run only when needed:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Guidance:
- Run `composer install --no-dev --optimize-autoloader` only when Composer dependencies changed.
- Run `npm ci` and `npm run build` only when frontend or Node dependencies changed.

## Database

Run only when migrations changed:

```bash
php artisan migrate --force
```

Run only on a fresh database:

```bash
php artisan db:seed --force
```

## Cache

Run for backend/PHP changes:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Queue Worker

Start:

```bash
pm2 start "php artisan queue:work --tries=1 --timeout=0" --name qrevents-queue
pm2 save
```

Restart after deploy:

Run only when queue-related code changed:

```bash
php artisan queue:restart
pm2 restart qrevents-queue
```

Check:

```bash
pm2 status
pm2 logs qrevents-queue
```

## Scheduler

The scheduler should already be configured on the server. Verify it with:

```bash
php artisan schedule:list
```

## Health Checks

```bash
php artisan about
tail -f storage/logs/laravel.log
```

## Browser Checks

- Open the site
- Log in
- Test Google login
- Open dashboard
- Create an event
- Open Stripe checkout for an unpaid event
- If the change was business-auth related, verify [https://eventsmart.app/register/business](https://eventsmart.app/register/business)
