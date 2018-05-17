# SonWEB
SonWEB is an administrative Website for Devices flashed with [Sonoff-Tasmota](https://github.com/arendst/Sonoff-Tasmota).   
You can find it here: [SonWEB GitHub](https://github.com/reloxx13/SonWEB)

## Features
* Login protected
* Multi Update Process
  * Select devices to update
  * Automatic Modus downoads latest firmware bin from Tasmota GitHub
* Show device information
* Mobile Responsive (Bootstrap4)
* Config devices
* SelfUpdate function for SonWEB
* NightMode (Enable/Disable/Auto) in settings
* AutoScan to find Tasmota Devices
* Support for multiple sensors

## Requirements

### Windows
I provide a ready2use ZIP on the releases page with XAMPP

### Unix
* A Webserver
  * recommend apache2
  * recommend php7 (works with php5, too)
  * php-curl php-zip Modules installed

### Docker
SonWEB is available in a docker image: [raymondmm/sonoff-docker](https://hub.docker.com/r/raymondmm/sonweb/), which is a Linux Alpine (3.7) based image with Apache2 and Php7 installed and supports **arm** (i.e. Raspberry PI3) and **amd64** (i.e. Synology DSM). Check out the [Wiki for running SonWEB in a Docker Container](https://github.com/RaymondMouthaan/sonweb-docker/wiki) for install instructions.


## Installation
### Windows
1. Download the XAMPP Zip package from the releases page
2. Extract the Zip (recommend to put the xamp folder in C:\
3. Run once the xamp\setup_xampp.bat
4. Start xampp-control.exe
5. Start Apache in the opened ControlCenter
6. Get your local IP Address

Now you can type in your browser http://YOURLOCALIP and SonWEB shows up.

### Unix
For unix its a bit harder. You need to install a web server with php-zip and php-curl modules installed. Also mod_rewrite must be enabled. I suggest to look in the [Guide for Ubuntu Server 16.04](https://github.com/reloxx13/SonWEB/wiki/Guide-for-Ubuntu-Server-16.04) and try to adjust it to your server OS.

## Example Images
![Login](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/1.png)
![Startpage](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/2.png)
![Devices](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/3.png)
![Device Add/Edit](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/3_1.png)
![Device Config_GENERAL](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/4.png)
![Device Config_Network](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/4_1.png)
![Device Update 1](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/5.png)
![Device Update 2](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/5_1.png)
![Device Update 3](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/5_2.png)
![Settings](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/6.png)
![Settings](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/7.png)

![Navi_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/m1.png)
![Startpage_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/m2.png)
![Devices_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/m3.png)
![Config_General_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/m4.png)
![Config_Network_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/readme/m4_1.png)

### Docker
[![Build Status](https://travis-ci.org/RaymondMouthaan/sonweb-docker.svg?branch=master)](https://travis-ci.org/RaymondMouthaan/SonWEB)
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
It's __highly recommended__ to use the volume option to persist/store your SonWEB config outside the container.

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
