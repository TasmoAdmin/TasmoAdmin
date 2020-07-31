ARG BUILD_FROM=arm32v6/alpine:3.7
FROM ${BUILD_FROM}

# Build arguments
ARG BUILD_DATE
ARG BUILD_REF
ARG BUILD_VERSION

# Label
LABEL \
    maintainer1="Reloxx <reloxx@interia.pl>" \
    maintainer2="Raymond M Mouthaan <raymondmmouthaan@gmail.com>" \
    org.label-schema.build-date=${BUILD_DATE} \
    org.label-schema.license="GNU" \
    org.label-schema.name="TasmoAdmin" \
    org.label-schema.version=${BUILD_VERSION} \
    org.label-schema.description="TasmoAdmin - An administrative Website for Devices flashed with Tasmota firmware." \
    org.label-schema.url="https://github.com/reloxx13/TasmoAdmin" \
    org.label-schema.usage="https://github.com/reloxx13/TasmoAdmin/blob/master/README.md" \
    org.label-schema.vcs-ref=${BUILD_REF} \
    org.label-schema.vcs-url="https://github.com/reloxx13/TasmoAdmin"

# Setup Qemu
ARG QEMU_ARCH
COPY tmp/qemu-${QEMU_ARCH}-static /usr/bin/qemu-${QEMU_ARCH}-static

# Install base system
ARG BUILD_ARCH=arm32v6
RUN \
    apk add --no-cache --virtual .build-dependencies \
        tar \
    \
    && apk add --no-cache \
        apk-tools \
        bash \
        busybox \
        ca-certificates \
        curl \
        musl-utils \
        musl \
        tzdata \
        nginx \
        php7-curl \
        php7-fpm \
        php7-json \
        php7-session \
        php7-zip \
        php7 \
    \
    && if [[ "${BUILD_ARCH}" = "arm32v6" ]]; then S6_ARCH="armhf"; else S6_ARCH="${BUILD_ARCH}"; fi \
    \
    && curl -L -s "https://github.com/just-containers/s6-overlay/releases/download/v1.21.4.0/s6-overlay-${S6_ARCH}.tar.gz" \
        | tar zxf - -C / \
    \
    && apk del --purge .build-dependencies \
    \
    && rm -f -r /tmp/*

# Environment variables
ENV BUILD_VERSION ${BUILD_VERSION}
ENV BUILD_REF ${BUILD_REF}
ENV SSL false

RUN echo "fastcgi_param BUILD_VERSION ${BUILD_VERSION};" >> /etc/nginx/fastcgi_params
RUN echo "fastcgi_param BUILD_REF ${BUILD_REF};" >> /etc/nginx/fastcgi_params

# Copy root filesystem
COPY .docker/rootfs /

# Setup application
COPY tasmoadmin /var/www/tasmoadmin
RUN find /var/www/tasmoadmin -type f -name ".htaccess" -depth -exec rm -f {} \; \
    && find /var/www/tasmoadmin -type f -name ".empty" -depth -exec rm -f {} \;

# Volumes
VOLUME [ "/data" ]

# Expose
EXPOSE 80 443

# Entrypoint
ENTRYPOINT [ "/init" ]
