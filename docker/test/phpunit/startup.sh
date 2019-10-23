#!/bin/sh
composer install
while ! mysqladmin ping -h sdcbar_db > /dev/null 2>&1
do
  sleep 1
done
while :;do sleep 1;done
