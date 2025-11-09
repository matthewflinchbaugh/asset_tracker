#!/bin/bash

php artisan optimize:clear >/dev/null || true
php artisan config:clear >/dev/null || true
php artisan cache:clear >/dev/null || true
php artisan view:clear >/dev/null || true

