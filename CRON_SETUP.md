# Cron Job Setup for Data Fetching

## Available Commands

The following artisan commands are available for fetching data:

- `php artisan fetch:slides` - Fetch slides data from all clients
- `php artisan fetch:devices` - Fetch device data from all clients
- `php artisan fetch:all` - Fetch both slides and device data

## Options

All commands support the `--sync` option to run synchronously instead of queuing:

```bash
php artisan fetch:slides --sync
php artisan fetch:devices --sync
php artisan fetch:all --sync
```

## Cron Setup

Add the following to your crontab (`crontab -e`) to run data fetching every 15 minutes:

```bash
*/15 * * * * cd /path/to/your/project && php artisan fetch:all --sync
```

Or run them separately:

```bash
*/15 * * * * cd /path/to/your/project && php artisan fetch:slides --sync
*/15 * * * * cd /path/to/your/project && php artisan fetch:devices --sync
```

## Job Run Times

The jobs automatically track their last run times in the `job_run_times` table:

- `FetchSlidesJob`
- `FetchDeviceDataJob`

You can check the last run times with:

```bash
php artisan tinker
>>> App\Models\JobRunTime::all()
```