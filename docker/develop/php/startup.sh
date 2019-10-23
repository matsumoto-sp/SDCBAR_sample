#!/bin/sh
composer install
while ! mysqladmin ping -h sdcbar_db > /dev/null 2>&1
do
  sleep 1
done
php -S sdcbar_php:8080
