<div align="center">


![Logo](/assets/logo.png)

[![Main](https://github.com/TasmoAdmin/TasmoAdmin/actions/workflows/main.yml/badge.svg)](https://github.com/TasmoAdmin/TasmoAdmin/actions/workflows/main.yml)
[![codecov](https://codecov.io/gh/TasmoAdmin/TasmoAdmin/branch/master/graph/badge.svg?token=8CWi1DIIjP)](https://codecov.io/gh/TasmoAdmin/TasmoAdmin)
![Discord](https://img.shields.io/discord/401474444914196490)
[![GitHub release](https://img.shields.io/github/release/TasmoAdmin/TasmoAdmin.svg)](https://GitHub.com/TasmoAdmin/TasmoAdmin/releases/)
[![GitHub contributors](https://img.shields.io/github/contributors/TasmoAdmin/TasmoAdmin.svg)](https://GitHub.com/TasmoAdmin/TasmoAdmin/graphs/contributors/) 
[![GitHub stars](https://img.shields.io/github/stars/TasmoAdmin/TasmoAdmin.svg)](https://github.com/TasmoAdmin/TasmoAdmin/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/TasmoAdmin/TasmoAdmin.svg)](https://github.com/TasmoAdmin/TasmoAdmin/network)
[![Github all releases](https://img.shields.io/github/downloads/TasmoAdmin/TasmoAdmin/total.svg?label=gh%20downloads)](https://GitHub.com/TasmoAdmin/TasmoAdmin/releases/) 
[![GitHub license](https://img.shields.io/github/license/TasmoAdmin/TasmoAdmin.svg)](https://github.com/TasmoAdmin/TasmoAdmin/blob/master/LICENSE)
[![bootstap](https://img.shields.io/badge/bootstrap-v4.5.x-%23563d7c.svg)](https://getbootstrap.com/)
[![php](https://img.shields.io/badge/php-8.2.x-%238892BF.svg)](https://secure.php.net/)

</div>

# TasmoAdmin

TasmoAdmin (previously SonWEB) is an administrative platform for devices flashed with [Tasmota](https://github.com/arendst/Tasmota).   
You can find it here: [TasmoAdmin GitHub](https://github.com/TasmoAdmin/TasmoAdmin).

It can run standalone, as a container, or as a Home Assistant addon.

## Features
* Login protected
* Multi update process
  * Select devices to update
  * Automatic Modus downloads latest firmware bin from Tasmota GitHub
* Show device information
* Mobile Responsive (Bootstrap4)
  * SCSS & Minified
* Config devices
* Self-update function for TasmoAdmin (disabled for Docker installs)
* NightMode (Enable/Disable/Auto) in settings
* AutoScan to find Tasmota Devices
* Support for multiple sensors
* Send Command to selected Devices

### Supported Platforms
* Apache2 and Nginx
* Docker by @RaymondMouthaan
  * unRaid by @digiblur
* IOCage (FreeNAS) by @tprelog

## YouTube
[![YouTube Video by DrZzs](https://img.youtube.com/vi/vJUhRyi3-BQ/0.jpg)](https://www.youtube.com/watch?v=vJUhRyi3-BQ)    
by DrZzs

## Setup

### Docker

TasmoAdmin is available as a Docker image on [GitHub packages](https://github.com/orgs/TasmoAdmin/packages/container/package/tasmoadmin).

This is a Linux Alpine based image with Nginx and PHP 8.2 installed. It supports multiple architectures, **amd64** (i.e. Synology DSM), **arm** (i.e. Raspberry PI3) and  **arm64** (i.e. Pine64). Check out the [Guide for TasmoAdmin on Docker](https://github.com/reloxx13/TasmoAdmin/wiki/Guide-for-TasmoAdmin-on-Docker) for setup instructions.

This is the recommended way to get up and running.

### Home Assistant Addon

TasmoAdmin is also available as [Home Assistant](https://www.home-assistant.io/) addon, please refer to [Home Assistant - addon TasmoAdmin](https://github.com/hassio-addons/addon-tasmoadmin) for more information.

### Using a Web Server

TasmoAdmin should run on any webserver that supports PHP 8.1+

Check the [guides](https://github.com/TasmoAdmin/TasmoAdmin/wiki) on the Wiki for more information.

## Configuration

Some environment variables are configured to allow easier customisation of the application

- `TASMO_DATADIR` - Path where to store data. If not provided defaults to `./tasmoadmin/data`
- `TASMO_BASEURL` - Customise the base URL for the application

## Development

Provided is a docker-compose setup to ease getting started.

Simply run:

```bash
make dev
```

Then visit http://localhost:8000

Persistent storage within this setup is located in the `.storage` folder.


## Translations

We use [Transifex][transifex] to maintain translations of this project. If you are not familiar with this service, you can read [Transifex Documentation][transifex-docs] to get started.


### Add or update translations

Here are steps to translate the extension to a specific language.

1. Join [our team][transifex-team] on Transifex.
2. Translate resources using Transifex web interface.

## Support

<img src="https://resources.jetbrains.com/storage/products/company/brand/logos/jb_beam.png" alt="JetBrains Logo (Main) logo." width="150" >

This project supported by JetBrains through their [Licenses for Open Source](https://www.jetbrains.com/community/opensource/) program.

## Example Images

#### Login Page
![Login](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/1.png)
#### Start Page
![Startpage](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/2.png)
#### Devices Page
![Devices](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/3.png)
Edit in Table   
![LiveChange](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/readme/livechange.gif)
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


[transifex-docs]: https://docs.transifex.com/getting-started-1/translators
[transifex-team]: https://explore.transifex.com/tasmoadmin/tasmoadmin/
[transifex]: https://www.transifex.com/
