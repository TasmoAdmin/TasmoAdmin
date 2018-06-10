#!/bin/bash

# pkg install git nginx php72 php72-zip php72-curl php72-json php72-session php72-filter
# git clone https://github.com/tprelog/TasmoAdmin.git /root/TasmoAdmin

cp -R /root/TasmoAdmin/tasmoadmin /usr/local/www/tasmoadmin

find /usr/local/www/tasmoadmin -type f -name ".htaccess" -depth -exec rm -f {} \;
find /usr/local/www/tasmoadmin -type f -name ".empty" -depth -exec rm -f {} \;

cp /usr/local/etc/nginx/nginx.conf /usr/local/etc/nginx/nginx.conf~
cp /root/TasmoAdmin/.iocage/overlay/usr/local/etc/nginx/nginx.conf /usr/local/etc/nginx/nginx.conf

cp /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf~
cp /root/TasmoAdmin/.iocage/overlay/usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

chown -R www:www /usr/local/www/tasmoadmin

sysrc -f /etc/rc.conf nginx_enable=YES
sysrc -f /etc/rc.conf php_fpm_enable=YES

service nginx start  2>/dev/null
service php-fpm start  2>/dev/null
