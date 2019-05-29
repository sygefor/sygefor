#!/bin/sh

sudo chown conjecto:www-data ../app/cache ../app/logs ../var/Material ../var/Publipost ../web -R
sudo chmod g+rwx ../app/cache ../app/logs ../var/Material ../var/Publipost ../web -R
php ../app/console cache:clear
php ../app/console cache:clear --env=prod

php ../app/console fos:js-routing:dump
gulp build:dist && gulp build:dist
php ../app/console doctrine:schema:update --complete --dump-sql
sudo chown conjecto:www-data ../app/cache ../app/logs ../var/Material ../var/Publipost ../web -R
sudo chmod g+rwx ../app/cache ../app/logs ../var/Material ../var/Publipost ../web -R

sudo service php5-fpm restart
sudo service apache2 restart
sudo service nginx restart
