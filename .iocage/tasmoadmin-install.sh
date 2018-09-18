#!/bin/bash

# pkg install bash git nginx php72 php72-zip php72-curl php72-json php72-session php72-filter
# git clone https://github.com/tprelog/TasmoAdmin.git /root/TasmoAdmin
# bash /root/TasmoAdmin/.iocage/tasmoadmin-install.sh

cp -R /root/TasmoAdmin/tasmoadmin /usr/local/www/tasmoadmin

find /usr/local/www/tasmoadmin -type f -name ".htaccess" -depth -exec rm -f {} \;
find /usr/local/www/tasmoadmin -type f -name ".empty" -depth -exec rm -f {} \;

mv /usr/local/etc/nginx/nginx.conf /usr/local/etc/nginx/nginx.conf~
cp /root/TasmoAdmin/.iocage/overlay/usr/local/etc/nginx/nginx.conf /usr/local/etc/nginx/nginx.conf

mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf~
cp /root/TasmoAdmin/.iocage/overlay/usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

chown -R www:www /usr/local/www/tasmoadmin

sysrc -f /etc/rc.conf nginx_enable=YES
sysrc -f /etc/rc.conf php_fpm_enable=YES

service nginx start  2>/dev/null
service php-fpm start  2>/dev/null


colors () {                               # Defien Some Colors for Messages
  grn=$'\e[1;32m'
  blu=$'\e[1;34m'
  cyn=$'\e[1;36m'
  end=$'\e[0m'
}
colors

v2srv_ip=$(ifconfig | sed -En 's/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p')

end_report () {                 # read all about it!
  echo; echo; echo; echo
    echo " ${blu}Status Report: ${end}"; echo
    echo "    $(service nginx status)"
    echo "  $(service php-fpm status)"
  echo   
    echo " ${cyn}TasmoAdmin${end}: ${grn}http://${v2srv_ip}${end}"
  echo
  echo; echo " ${blu}Finished!${end}"; exit    
}
end_report
