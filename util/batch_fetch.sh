#!/bin/bash
# Run both fetch:slides and fetch:devices artisan commands
# Usage: Add to cron for scheduled batch fetches

/usr/bin/php artisan fetch:slides  --sync
/usr/bin/php artisan fetch:devices  --sync