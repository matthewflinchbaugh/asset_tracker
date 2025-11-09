# Asset Tracker (Laravel)

## Requirements
- PHP version X.Y
- Composer
- MariaDB

## Setup
```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

