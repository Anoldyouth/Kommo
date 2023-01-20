#!/bin/bash

TYPE=update

NAME=$(date +%s)

if /var/www/application/vendor/bin/laminas Sync:add-worker -t $TYPE -w "$NAME"; then
    /var/www/application/vendor/bin/laminas Sync:start-update-tokens-worker; /var/www/application/vendor/bin/laminas Sync:delete-worker -t $TYPE -w "$NAME"
fi
