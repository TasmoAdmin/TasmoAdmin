#!/usr/bin/with-contenv bash
# ==============================================================================
# Community Hass.io Add-ons: SonWEB
# Ensures data is store in a persistent location
# ==============================================================================
# shellcheck disable=SC1091
#source /usr/lib/hassio-addons/base.sh

if [ ! -f "/var/www/tasmoadmin/.dockerenv" ]; then
    echo 'Docker env does not exist, doing that now...'

    # Create .docker
    touch /var/www/tasmoadmin/.dockerenv

    # Ensure file permissions
    chown -R nginx:nginx /var/www/tasmoadmin/.dockerenv
fi
