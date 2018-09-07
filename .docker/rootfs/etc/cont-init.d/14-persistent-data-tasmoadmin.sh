#!/usr/bin/with-contenv bash
# ==============================================================================
# Community Hass.io Add-ons: TasmoAdmin
# Ensures data is store in a persistent location
# ==============================================================================
# shellcheck disable=SC1091
#source /usr/lib/hassio-addons/base.sh

# Setup structure if it does not exists
if [ ! -d "/data/tasmoadmin" ]; then
    echo 'Data directory not initialized, doing that now...'
    mkdir -p /data/tasmoadmin
    cp -Rv /var/www/tasmoadmin/data/* /data/tasmoadmin
fi

# Create /data/tasmoadmin/firmware if it does not exists
if [ ! -d "/data/tasmoadmin/firmwares" ]; then
    mkdir -p /data/tasmoadmin/firmwares
    echo "/data/tasmoadmin/firmwares created."
fi

# Create /data/tasmoadmin/updates if it does not exists
if [ ! -d "/data/tasmoadmin/updates" ]; then
    mkdir /data/tasmoadmin/updates
    echo "/data/tasmoadmin/updates created."
fi

echo 'Symlinking data/tasmoadmin directory to persistent storage location...'
rm -f -r /var/www/tasmoadmin/data
ln -s /data/tasmoadmin /var/www/tasmoadmin/data

# Ensure file permissions
chown -R nginx:nginx /data/tasmoadmin
find /data/tasmoadmin -not -perm 0644 -type f -exec chmod 0644 {} \;
find /data/tasmoadmin -not -perm 0755 -type d -exec chmod 0755 {} \;
