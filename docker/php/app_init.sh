#!/bin/ash

cd /var/www/html

composer update nothing

rm -f bootstrap/cache/*

php artisan key:generate

php artisan make:session-table -q
php artisan migrate --force
php artisan migrate:fresh

php artisan optimize:clear
php artisan optimize

php artisan sonar:netflow:initialize
