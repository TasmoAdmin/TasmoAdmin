#!/usr/bin/with-contenv bash
# ==============================================================================
# Community Hass.io Add-ons: TasmoAdmin
# Ensures data is store in a persistent location
# ==============================================================================
# shellcheck disable=SC1091
#source /usr/lib/hassio-addons/base.sh

# Setup structure if it does not exists
if [ ! -d "/data/nginx" ]; then
    echo 'Data nginx directory not initialized, doing that now...'
    mkdir -p /data/nginx/certs
    cp -Rv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.org
    cp -Rv /etc/nginx/nginx.conf.org /data/nginx/nginx.conf.org
    cp -Rv /data/nginx/nginx.conf.org /data/nginx/nginx.conf
    cp -Rv /etc/nginx/nginx-ssl.conf /data/nginx/nginx-ssl.conf
    cp -Rv /etc/nginx/certs/* /data/nginx/certs
fi


if [[ -L "/data/nginx/nginx.conf" ]]; then
	echo "ERROR: /data/nginx/nginx.conf is a symlink, try to download the default nginx.conf from github";
	rm -rf /data/nginx/nginx.conf
	wget -O /data/nginx/nginx.conf https://raw.githubusercontent.com/TasmoAdmin/TasmoAdmin/master/.docker/rootfs/etc/nginx/nginx.conf
    cp -Rv /data/nginx/nginx.conf /etc/nginx/nginx.conf.org
fi

echo 'Symlinking /etc/nginx.conf to persistent storage location...'
rm -rf /etc/nginx/nginx.conf
ln -s /data/nginx/nginx.conf /etc/nginx/nginx.conf
