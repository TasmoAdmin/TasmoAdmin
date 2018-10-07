#!/bin/sh

ta="/root/TasmoAdmin"
pdir="${0%/*}"

    # pkg install bash git nginx php72 php72-zip php72-curl php72-json php72-session php72-filter
    if [ "${pdir}" != "${ta}/.iocage" ]; then # Skip `git clone` if we're already in the 'TasmoAdmin/.iocage' directory
     git clone https://github.com/reloxx13/TasmoAdmin.git "${ta}"
    fi
    # bash /root/TasmoAdmin/.iocage/post_install.sh standard

if [ ! -d "/usr/local/www/tasmoadmin" ]; then
  mkdir -p "/usr/local/www/tasmoadmin"
fi; cp -R "${ta}"/tasmoadmin/ /usr/local/www/tasmoadmin

if [ "${1}" = "standard" ]; then    # Only cp files when installing a standard-jail

  mv /usr/local/etc/nginx/nginx.conf /usr/local/etc/nginx/nginx.conf.old
  cp "${ta}"/.iocage/overlay/usr/local/etc/nginx/nginx.conf /usr/local/etc/nginx/nginx.conf

  mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.old
  cp "${ta}"/.iocage/overlay/usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

  cp "${ta}"/.iocage/overlay/etc/motd /etc/motd

  if [ ! -d "/root/bin" ]; then
    mkdir -p "/root/bin"
  fi; cp "${ta}"/.iocage/overlay/root/bin/tasmo-pwreset /root/bin/tasmo-pwreset
  
fi

find /usr/local/www/tasmoadmin -type f -name ".htaccess" -depth -exec rm -f {} \;
find /usr/local/www/tasmoadmin -type f -name ".empty" -depth -exec rm -f {} \;
  
chown -R www:www /usr/local/www/tasmoadmin
chmod +x /root/bin/tasmo-pwreset

sysrc -f /etc/rc.conf nginx_enable=YES
sysrc -f /etc/rc.conf php_fpm_enable=YES

service nginx start  2>/dev/null
service php-fpm start  2>/dev/null

if [ "${1}" = "standard" ]; then
  v2srv_ip=$(ifconfig | sed -En 's/127.0.0.1//;s/.*inet (addr:)?(([0-9]*\.){3}[0-9]*).*/\2/p')
  
  colors () {                               # Defien Some Colors for Messages
    grn=$'\e[1;32m'
    blu=$'\e[1;34m'
    cyn=$'\e[1;36m'
    end=$'\e[0m'
  }; colors

  end_report () {                 # read all about it!
    echo; echo; echo; echo
        echo " ${blu}Status Report: ${end}"; echo
        echo "    $(service nginx status)"
        echo "  $(service php-fpm status)"
    echo   
        echo " ${cyn}TasmoAdmin${end}: ${grn}http://${v2srv_ip}${end}"
    echo
    echo; exit    
  }; end_report

fi

