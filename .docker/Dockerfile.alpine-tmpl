ARG BASE_IMAGE
FROM $BASE_IMAGE

# Build arguments
ARG BUILD_DATE
ARG BUILD_VERSION
ARG BUILD_REF
ARG QEMU_ARCH

# Basic build-time metadata as defined at http://label-schema.org
LABEL org.label-schema.build-date=$BUILD_DATE \
    org.label-schema.docker.dockerfile=".docker/Dockerfile.alpine-tmpl" \
    org.label-schema.license="GNU" \
    org.label-schema.name="TasmoAdmin" \
    org.label-schema.version=$BUILD_VERSION \
    org.label-schema.description="TasmoAdmin an administrative Website for Devices flashed with Tasmota firmware." \
    org.label-schema.url="https://github.com/reloxx13/TasmoAdmin" \
    org.label-schema.vcs-ref=$BUILD_REF \
    org.label-schema.vcs-type="Git" \
    org.label-schema.vcs-url="https://github.com/reloxx13/TasmoAdmin" \
    maintainer1="Reloxx" \
    maintainer2="Raymond M Mouthaan <raymondmmouthaan@gmail.com>"

# Copy qemu-*-static
COPY tmp/qemu-$QEMU_ARCH-static /usr/bin/qemu-$QEMU_ARCH-static

# Add Apache2 and PHP7
RUN apk update && apk upgrade && \
    apk add --no-cache \
        apache2 \
        php7 \
        php7-apache2 \
        git \
        curl \
        nano \
        php7-json \
        php7-session \
        php7-zip \
        php7-curl && \
    rm -f /var/cache/apk/* && \
    cp /usr/bin/php7 /usr/bin/php

# Configure Apache
RUN mkdir /run/apache2 \
    && sed -i "s/#LoadModule\ rewrite_module/LoadModule\ rewrite_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_module/LoadModule\ session_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_cookie_module/LoadModule\ session_cookie_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_crypto_module/LoadModule\ session_crypto_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ deflate_module/LoadModule\ deflate_module/" /etc/apache2/httpd.conf \
    && sed -i "s#^DocumentRoot \".*#DocumentRoot \"/app/tasmoadmin\"#g" /etc/apache2/httpd.conf \
    && sed -i "s#/var/www/localhost/htdocs#/app/tasmoadmin#" /etc/apache2/httpd.conf \
    && printf "\n<Directory \"/app/tasmoadmin\">\n\tAllowOverride All\n</Directory>\n" >> /etc/apache2/httpd.conf

# Setup TasmoAdmin Application
COPY tasmoadmin /app/tasmoadmin
COPY .docker/start.sh /bootstrap/start.sh
RUN chown -R apache:apache /app \
    && chmod -R 755 /app \
    && chmod +x /bootstrap/start.sh \
    && ln -s /app/tasmoadmin/data /data

VOLUME /data
EXPOSE 80

CMD ["/bootstrap/start.sh"]
