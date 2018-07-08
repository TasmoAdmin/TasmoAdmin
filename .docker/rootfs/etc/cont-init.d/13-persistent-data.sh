#!/usr/bin/with-contenv bash
# ==============================================================================
# Community Hass.io Add-ons: SonWEB
# Ensures data is store in a persistent location
# ==============================================================================
# shellcheck disable=SC1091
#source /usr/lib/hassio-addons/base.sh

if [ ! -d "/data" ]; then
    echo 'Data directory not initialized, doing that now...'

    # Setup structure
    mkdir -p /data
    cp -Rv /var/www/tasmoadmin/data /data
    find /var/www/tasmoadmin/data -type f -name ".htaccess" -depth -exec rm -f {} \;

fi


# Create /data/firmware if it does not exists
if [ ! -d "/data/firmwares" ]; then
    mkdir -p /data/firmwares
    echo "/data/firmwares created."

    find /data/firmwares -not -perm 0644 -type f -exec chmod 0644 {} \;
    find /data/firmwares -not -perm 0755 -type d -exec chmod 0755 {} \;
fi

# Create /data/updates if it does not exists
if [ ! -d "/data/updates" ]; then
    mkdir /data/updates
    echo "/data/updates created."

    find /data/updates -not -perm 0644 -type f -exec chmod 0644 {} \;
    find /data/updates -not -perm 0755 -type d -exec chmod 0755 {} \;
fi

echo 'Symlinking data directory to persistent storage location...'
rm -f -r /var/www/tasmoadmin/data
ln -s /data /var/www/tasmoadmin/data


# Ensure file permissions
chown -R nginx:nginx /data
find /data -not -perm 0644 -type f -exec chmod 0644 {} \;
find /data -not -perm 0755 -type d -exec chmod 0755 {} \;
