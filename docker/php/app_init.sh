#!/bin/ash

cd /var/www/html

composer update nothing

rm -f bootstrap/cache/*

php artisan key:generate

php artisan make:session-table -q
php artisan migrate --force
php artisan migrate:fresh

php artisan cache:clear
php artisan config:clear
php artisan view:clear

php artisan key:generate

php artisan route:cache
php artisan config:cache
php artisan optimize

php artisan sonar:netflow:initialize
