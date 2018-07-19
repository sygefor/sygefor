#!/bin/sh

php ../app/console cache:clear
php ../app/console cache:clear --env=prod
if [ "$#" -eq "0" ] ; then
    chown www-data:www-data ../app/cache ../app/logs ../var/Material ../var/Partner ../var/Templates -R
else
    chown $1:$1 ../app/cache ../app/logs ../var/Material ../var/Partner ../var/Templates -R
fi
# php ../app/console fos:js-routing:dump
# gulp build && gulp build
service php5-fpm restart
service apache2 restart
service nginx restart
