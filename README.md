# TasmoAdmin
TasmoAdmin is an administrative Website for Devices flashed with [Sonoff-Tasmota](https://github.com/arendst/Sonoff-Tasmota).   
You can find it here: [TasmoAdmin GitHub](https://github.com/reloxx13/TasmoAdmin).
It supports running on Windows, Linux and as Docker container.

## Features
* Login protected
* Multi Update Process
  * Select devices to update
  * Automatic Modus downloads latest firmware bin from Tasmota GitHub
* Show device information
* Mobile Responsive (Bootstrap4)
* Config devices
* SelfUpdate function for TasmoAdmin
* NightMode (Enable/Disable/Auto) in settings
* AutoScan to find Tasmota Devices
* Support for multiple sensors

## Installation

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

### Docker
TasmoAdmin is available as a Docker image at [Docker Hub](https://hub.docker.com/r/raymondmm/tasmoadmin/). This is a Linux Alpine (3.7) based image with Apache2 and Php7 installed. It supports multiple architectures, **amd64** (i.e. Synology DSM), **arm** (i.e. Raspberry PI3) and  **arm64** (i.e. Pine64). Check out the [Wiki for running TasmoAdmin in a Docker Container](https://github.com/RaymondMouthaan/tasmoadmin-docker/wiki) for install instructions.

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
