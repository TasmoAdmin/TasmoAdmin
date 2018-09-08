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
    mkdir -p /data/nginx
    cp -Rv /etc/nginx/nginx.conf /data/nginx/nginx.conf
    mkdir -p /data/nginx/certs
    cp -Rv /etc/nginx/certs/* /data/nginx/certs
fi

echo 'Symlinking /etc/nginx.conf to persistent storage location...'
rm -f -r /etc/nginx/nginx.conf
ln -s /data/nginx/nginx.conf /etc/nginx/nginx.conf
