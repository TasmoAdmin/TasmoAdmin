#!/usr/bin/with-contenv bash
# ==============================================================================
# Community Hass.io Add-ons: TasmoAdmin
# Ensures data is store in a persistent location
# ==============================================================================
# shellcheck disable=SC1091
#source /usr/lib/hassio-addons/base.sh

# Setup structure if it does not exists
if [ ! -d "/data/ngnix" ]; then
    echo 'Data directory not initialized, doing that now...'
    mkdir -p /data/ngnix
    cp -Rv /etc/nginx /data/nginx
fi

echo 'Symlinking data/nginx directory to persistent storage location...'
rm -f -r /etc/nginx
ln -s /data/nginx /etc/nginx

# Ensure file permissions
#chown -R nginx:nginx /data/tasmoadmin
#find /data/tasmoadmin -not -perm 0644 -type f -exec chmod 0644 {} \;
#find /data/tasmoadmin -not -perm 0755 -type d -exec chmod 0755 {} \;
