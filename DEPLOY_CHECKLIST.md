# QR Events Deploy Checklist

## Update Code

```bash
cd /home/wvdev/htdocs/wvdev.org
git pull origin main
```

## Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## Database

Run on every deploy:

```bash
php artisan migrate --force
```

Run only on a fresh database:

```bash
php artisan db:seed --force
```

## Cache

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

Cron entry:

```cron
* * * * * cd /home/wvdev/htdocs/wvdev.org && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Check:

```bash
crontab -l
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
