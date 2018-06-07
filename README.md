# TasmoAdmin

[![Build Status](https://travis-ci.org/reloxx13/TasmoAdmin.svg?branch=master)](https://travis-ci.org/reloxx13/TasmoAdmin)
[![DockerHub Pull](https://img.shields.io/docker/pulls/raymondmm/tasmoadmin.svg)](https://hub.docker.com/r/raymondmm/tasmoadmin/)
[![DockerHub Star](https://img.shields.io/docker/stars/raymondmm/tasmoadmin.svg?maxAge=2592000)](https://hub.docker.com/r/raymondmm/tasmoadmin/)

TasmoAdmin (previously SonWEB) is an administrative Website for Devices flashed with [Sonoff-Tasmota](https://github.com/arendst/Sonoff-Tasmota).   
You can find it here: [TasmoAdmin GitHub](https://github.com/reloxx13/TasmoAdmin).
It supports running on Windows, Linux, Docker container and as Home Assistant addon.

## Features
* Login protected
* Multi Update Process
  * Select devices to update
  * Automatic Modus downloads latest firmware bin from Tasmota GitHub
* Show device information
* Mobile Responsive (Bootstrap4)
* Config devices
* SelfUpdate function for TasmoAdmin (disabled for Docker)
* NightMode (Enable/Disable/Auto) in settings
* AutoScan to find Tasmota Devices
* Support for multiple sensors
* chat (beta)

## YouTube
[YouTube Video by DrZzs](https://www.youtube.com/watch?v=vJUhRyi3-BQ&feature=push-fr&attr_tag=3vmgYDcs_Jb9eQZs-6)

## Installation

### Docker
TasmoAdmin is available as a Docker image at [Docker Hub](https://hub.docker.com/r/raymondmm/tasmoadmin/). This is a Linux Alpine (3.7) based image with Nginx and Php7 installed. It supports multiple architectures, **amd64** (i.e. Synology DSM), **arm** (i.e. Raspberry PI3) and  **arm64** (i.e. Pine64). Check out the [Guide for TasmoAdmin on Docker](https://github.com/reloxx13/TasmoAdmin/wiki/Guide-for-TasmoAdmin-on-Docker) for install instructions.

### Home Assistant Addon
TasmoAdmin is available as Home Assistant addon, please refer to
[Home Assistant - addon TasmoAdmin](https://github.com/hassio-addons/addon-sonweb) for more information.

### Windows
A ready to use TasmoAdmin-XAMP-Portable-\*.zip is available on the [release page](https://github.com/reloxx13/TasmoAdmin/releases) and is based on XAMPP.

1. Download the XAMPP Zip package from the releases page
2. Extract the Zip (recommend to put the xamp folder on C:\
3. Run once the xamp\setup_xampp.bat
4. Start xampp-control.exe
5. Start Apache in the opened ControlCenter
6. Get your local IP Address

Now you can type in your browser http://YOURLOCALIP and TasmoAdmin shows up.

### Linux
Running TasmoAdmin on a Linux/Unix hosts requires the following:
* A Webserver
  * apache2 recommended
  * php7 recommended (works with php5 too)
  * php-curl php-zip Modules installed

You need to install a web server with php-zip and php-curl modules installed. Also mod_rewrite must be enabled. I suggest to look in the [Guide for Ubuntu Server 16.04](https://github.com/reloxx13/TasmoAdmin/wiki/Guide-for-Ubuntu-Server-16.04) and try to adjust it to your server OS.

## Example Images
#### Login Page
![Login](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/1.png)
#### Start Page
![Startpage](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/2.png)
#### Devices Page
![Devices](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/3.png)
#### Devices Add/Edit Page
![Device Add/Edit](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/3_1.png)
#### Config General Page
![Device Config_GENERAL](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/4.png)
#### Config Network Page
![Device Config_Network](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/4_1.png)
#### Update Devices Page
![Device Update 1](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/5.png)
![Device Update 2](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/5_1.png)
![Device Update 3](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/5_2.png)
#### Settings Page
![Settings](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/6.png)
![Settings](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/7.png)

#### Mobile
![Navi_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/m1.png)
![Startpage_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/m2.png)
![Devices_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/m3.png)
![Config_General_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/m4.png)
![Config_Network_M](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/m4_1.png)
