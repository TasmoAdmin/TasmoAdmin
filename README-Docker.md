Sonweb-docker
================
A Docker image for SonWeb. [SonWEB](https://github.com/reloxx13/SonWEB) is an administrative Website for Devices flashed with [Sonoff-Tasmota](https://github.com/arendst/Sonoff-Tasmota).

[![Build Status](https://travis-ci.org/RaymondMouthaan/sonweb-docker.svg?branch=master)](https://travis-ci.org/RaymondMouthaan/sonweb-docker)
[![This image on DockerHub](https://img.shields.io/docker/pulls/raymondmm/sonweb.svg)](https://hub.docker.com/r/raymondmm/sonweb/)

This image is based on **Alpine** with Apache2 and PHP7 installed. It adds qemu-\*-static and uses manifest-tool to push manifest list to docker hub.

## Architectures
Currently supported architectures:
- **linux-amd64** (i.e. Synology NAS)
- **linux-arm** (i.e. Raspberry PI 1, 2, 3)
- **linux-arm64** (i.e. Pine64)

## Usage
### docker run
```docker run -it -p <host_port:container_port> -e APACHE_SERVER_NAME=<server_name> --name <container_name> raymondmm/sonweb```

example:
```
docker run -it -p 9999:80 -e APACHE_SERVER_NAME=mysonweb.mydomain --name sonweb raymondmm/sonweb
```

```
docker run                  - run this container... and build locally if necessary first.
      -it                   - attach a terminal session so we can see what is going on.
      -p 9999:80            - connect local port 80 to the exposed internal port 9999.
      -e APACHE_SERVER_NAME=mysonweb.mydomain - set apache server name to avoid warning at startup.
      --name sonweb         - give this container a friendly local name.
      raymondmm/sonweb      - the image to base it on
```

### Persistent (highly recommended)
It's __highly recommended__ to use the volume option to persist/store your Sonweb config outside the container.

```
docker run -it -v <directory_host>:<directory_container> -p <host_port:container_port> -e APACHE_SERVER_NAME=<server_name> --name <container_name> raymondmm/sonweb
```

example:
```
docker run -it -v /host/data:/data -p 9999:80 -e APACHE_SERVER_NAME=mysonweb.mydomain --name sonweb raymondmm/sonweb
```

```
docker run                  - run this container... and build locally if necessary first.
      -it                   - attach a terminal session so we can see what is going on.
      -v /host/data:/data   - /host/data is a host directory which is linked to the /data directory inside the container.
      -p 9999:80            - connect local port 9999 to the exposed internal port 80.
      -e APACHE_SERVER_NAME=mysonweb.mydomain - set apache server name to avoid warning at startup.
      --name sonweb         - give this container a friendly local name.
      raymondmm/sonweb      - the image to base it on
```

### docker stack
```
docker stack deploy emqtt --compose-file docker-compose-emqtt.yml
```

Example of docker-compose.yml

```
version: "3.4"

services:
  sonweb:
    image: raymondmm/sonweb
    hostname: sonweb
    environment:
      - "APACHE_SERVER_NAME=sonweb.indonesia"
    networks:
      - sonweb-net

    volumes:
      - type: bind
        source: /sonweb/data/
        target: /data

      - type: bind
        source: /etc/localtime
        target: /etc/localtime
        read_only: true

      - type: bind
        source: /etc/timezone
        target: /etc/timezone
        read_only: true

    deploy:
      replicas: 1

networks:
  sonweb-net:
    external: true
```

For more details please read the [Wiki](https://github.com/RaymondMouthaan/sonweb-docker/wiki)
