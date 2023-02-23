#!/bin/bash

chown -R www-data:www-data /var/www/html
chown -R www-data:www-data /data/tasmoadmin

apache2-foreground
