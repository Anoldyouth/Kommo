#!/bin/bash
# turn on bash's job control
set -m
# Start the "main" PHP process and put it in the background
php-fpm &
# Clear all workers
/var/www/application/vendor/bin/laminas Sync:clear-workers -t 'update'
# Start the helper crond process
crond
# now we bring the primary process back into the foreground
fg %1

