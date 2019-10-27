# CHANGELOG


## Upcoming
- **UPDATE**: Support for LM75AD Sensor [#275](https://github.com/reloxx13/TasmoAdmin/issues/275)

### v1.6.1-beta2
-FIX: login box position fix
- **UPDATE**: Change to new Tasmota Github URL
- FIX: loop 3rd an 4th ip block in autoscan to support bigger networks [#302](https://github.com/reloxx13/TasmoAdmin/issues/302), thx @[Leuselator](https://github.com/Leuselator) 
  - Note: Autoscan will take longer now
- **UPDATE**: Change htaccess to new syntax [#299](https://github.com/reloxx13/TasmoAdmin/issues/299), thx @[joba-1](https://github.com/joba-1)
- FIX: Remove PHP5 support, add SELinux info to readme [#290](https://github.com/reloxx13/TasmoAdmin/issues/290)


### v1.6.1-beta1
- FIX: Remove SSL Keys 
- **UPDATE**: Support multi BMP´s ([pr#4195](https://github.com/arendst/Tasmota/pull/4195))
  - BME280-76/77
- adjust some colors  
- **FIX**: IOS/Mobile Scroll bug [#244](https://github.com/reloxx13/TasmoAdmin/issues/244) 
- **FIX**: Startpage Devices without Relais shaking and breaking js [#97](https://github.com/reloxx13/TasmoAdmin/issues/97)  

    
### v1.6.0
- FIX: startpage all off icon error         
- **UPDATE**: Support Sensor BMP180 [#224](https://github.com/reloxx13/TasmoAdmin/issues/224)
- FIX: login page horizontal scrollable removed
- FIX: JS bug caused devices not to load (non-docker only bug) #225 #226
- DEV: CSS & HTML fixes
- **NEW**: support ssl / https added [#113](https://github.com/reloxx13/TasmoAdmin/issues/113) 
  
To use TasmoAdmin with ssl:

```
sudo docker run -d -p 5443:443 -e SSL=true -v /home/pi/tasmoadmin:/data --name=TasmoAdmin --restart=always raymondmm/tasmoadmin:beta
```
Using self-signed root certificate give a warning in your browser and is not secure.

You can add your own certificate by replacing tasmoadmin.key and tasmoadmin.crt which are stored on your host, i.e. /home/pi/tasmoadmin/nginx/certs

- **FIX**: Space in mobile 
- **FIX**: Display new release version available for betas
- **NEW**: nginx config is now modifyable by user if needed

##### Custom nginx.config
1. If you want to use your own (custom) nginx.config, then just place it in your local data volume you started the docker container with like **/home/pi/tasmoadmin/data/nginx/** and replace existing nginx.config.

2. (Re-)Start your container to use custom nginx.config.
            
- **FIX**: Detection of StateText change [#199](https://github.com/reloxx13/TasmoAdmin/issues/199), [#154](https://github.com/reloxx13/TasmoAdmin/issues/154)   
- CI skipped, use v1.6.0-beta8
- **FIX**: XAMPP Folder Structure in ZIP
- **DEV**: Lower zip splitted parts filesize for git
- **FIX**: avoid sending backlog commands twice cuz backlog does not give any response. [#210](https://github.com/reloxx13/TasmoAdmin/issues/210)
- **FIX**: Unwanted underscore added [#210](https://github.com/reloxx13/TasmoAdmin/issues/210)
- **FIX**: XAMPP Pack fixed and updated to XAMPP 7.2.9.0 (PHP7) [141](https://github.com/reloxx13/TasmoAdmin/issues/141), [209](https://github.com/reloxx13/TasmoAdmin/issues/209)

- **FIX**: nginx config
- **CHANGE**:  HTTP/HTTPS Detection [#198](https://github.com/reloxx13/TasmoAdmin/issues/198)
   - New Checkbox to choose http/https, default is http.   
![grafik](https://user-images.githubusercontent.com/14855001/45046243-e2f26a00-b075-11e8-9304-8621cc6e0ba5.png)
- **UPDATE**: Support Sensor DS18B20 [#202](https://github.com/reloxx13/TasmoAdmin/issues/202)
- DEV: Add robots/search crawler protection
- **FIX**: Add device password field as password not text input [#184](https://github.com/reloxx13/TasmoAdmin/issues/184)
- **FIX**: for new tasmota changelog structure [tasmota-commit#ace6180](https://github.com/arendst/Tasmota/commit/ace6180e67a29926fade72ab10015c18b07c973e)    
- **NEW**: CZ language ( Big thanks @Vladimir S. by Mail) 
- **FIX**: Update Tasmota language Releases for auto updates.    
- **UPDATE**: Font Awesome 5.0.13 -> 5.3.1   
- **UPDATE**: Bootstrap 4.1.1 -> 4.1.2
- **CHANGE**: Startpage rework:
![startpage_v1.2.PNG](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/changelog/startpage_v1.2.PNG)   
- **DEV**: Some more Error Catchings and debugs on error for lost configs [#207](https://github.com/reloxx13/TasmoAdmin/issues/207)
- **NEW**: Detection of StateText change [#199](https://github.com/reloxx13/TasmoAdmin/issues/199), [#154](https://github.com/reloxx13/TasmoAdmin/issues/154) 

## v1.5.4
- **FIX** autoload case-sensitive [#182](https://github.com/reloxx13/TasmoAdmin/issues/182)  
   
## v1.5.3
- **FIX** another fix for chrome ERR_RESPONSE_HEADERS_TOO_BIG [#182](https://github.com/reloxx13/TasmoAdmin/issues/182)
   
   
## v1.5.2
- **FIX** Chrome crying ERR_RESPONSE_HEADERS_TOO_BIG [#182](https://github.com/reloxx13/TasmoAdmin/issues/182)

## v1.5.1
- **FIX** Update Tag info [#180](https://github.com/reloxx13/TasmoAdmin/issues/180)


## v1.5.0
- **NEW**: Link Tasmota Changelog Issues [#173](https://github.com/reloxx13/TasmoAdmin/issues/173)
- **NEW**: Use json file for for config data
- **NEW**: Config is now saved in a cookie
  - Cookies are stored in the Clients Browser, reduces Reads on Servers Drive. 
  - Some uneccessery writes removed, boom.
  - Note: Encrypted User PW will not be saved in the Cookie.   
  Old Config File will be migrated to json and removed in a later release. Stays as Backup.   
  If you delete the MyConfig.json, it will generated new based on the MyConfig.php
- **DEV**: Use SCSS and Minified CSS/JS Resources, gives some performence boost and save network traffic.
- **NEW**: Get Current Tag from Docker [#176](https://github.com/reloxx13/TasmoAdmin/issues/176)  
- **FIX**: $_SERVER REQUEST_SCHEME is not reliable [#174](https://github.com/reloxx13/TasmoAdmin/issues/174)    
   
## v1.4.0
- **FIX**: set session dir inside tasmota dir instead of server root /tmp [#169](https://github.com/reloxx13/TasmoAdmin/issues/169)
- **FIX**: go to selected homepage after login [#115](https://github.com/reloxx13/TasmoAdmin/issues/115) 
- **NEW**: Disable update check option [#156](https://github.com/reloxx13/TasmoAdmin/issues/156) 
- **NEW**: Add top scrollbar on device list [#170](https://github.com/reloxx13/TasmoAdmin/issues/170) 
- MINOR: Rename "Show More" => "Detail View" in i18n.EN [#168](https://github.com/reloxx13/TasmoAdmin/issues/168) 
- **NEW**: Support Multi Friendlyname on Device General Config Page (fw >= 5.12.0h) [Tasmota#3161](https://github.com/arendst/Tasmota/issues/3161)
- **FIX**: device config allow more steps for Sleep [#172](https://github.com/reloxx13/TasmoAdmin/issues/172)      


## v1.3.3 
- MINOR: fix for travis building   
   
## v1.3.2   
- **FIX**: fix device update, use ota magic [#165](https://github.com/reloxx13/TasmoAdmin/issues/165) [#166](https://github.com/reloxx13/TasmoAdmin/issues/166) 

## v1.3.1   
- **NEW**: MQTT Config Tab on device config page [#101](https://github.com/reloxx13/TasmoAdmin/issues/101)
  - ENHANCEMENT: Only changed config values will be send to the device
- MINOR: style show more btn responsive [#145](https://github.com/reloxx13/TasmoAdmin/issues/145)


## v1.2.1
**HOTFIX**: Fix edit device #147

## v1.2.0
- **FIX**: some Tasmota 5.10.0 json #131 [hassio-addon#6](https://github.com/hassio-addons/addon-tasmoadmin/issues/6)
- **UPDATE**: Font Awesome 5.0.6 -> 5.1.0
- **DOCKER**: Show Changelog Link in helpmenu [#108](https://github.com/reloxx13/TasmoAdmin/issues/108)
- MINOR: make footer smaller and sticky, darker link color in nightmode
  - top navi made a lil bit smaller, too
- MINOR: catch and remove control chars in json response [#78](https://github.com/reloxx13/TasmoAdmin/issues/78)
- **NEW**: Show new update available icon in footer
- MINOR: Possible performence fixes for checkNightmode
- MINOR: cache resources by release tag version

## v1.1.0                              
- **NEW**: you can now set a default startpage [#115](https://github.com/reloxx13/TasmoAdmin/issues/115)
- **FIX**: Multichannel devices don't restore correctly if backend fails temporarily #121          
- MINOR: dont be blind on page reload: set nightmode class initial if nightmode is enabled


## v1.0.7               
- **NEW**: add send command within device list [#100](https://github.com/reloxx13/TasmoAdmin/issues/100)
- MINOR: error handling for selfupdate if travis build failed [#142](https://github.com/reloxx13/TasmoAdmin/issues/142)       
- MINOR: restyle show more

## v1.0.6
- **DOCKER**: update busybox to fix travis build

## v1.0.4 - 1.0.5
- **FIX**: date in update [#124](https://github.com/reloxx13/TasmoAdmin/issues/124)
- MINOR: change filename to load js file correctly (only firefox issue?)
  - fixes the show more checkbox 
- **NEW**: add configurable server port [#122](https://github.com/reloxx13/TasmoAdmin/issues/122),[#131](https://github.com/reloxx13/TasmoAdmin/issues/131)
- MINOR: show some energy data
- **NEW**: add iocage support by @tprelog :)

## v0.0.8 - v 1.0.3
- MINOR: add logo
- **UPDATE**: Font Awesome 5.0.6 -> 5.0.13
- **UPDATE**: bootstrap 4.0.0 -> 4.1.1
- **DOCKER**: clean-up unnecessary lines to reduce image size
- **DOCKER**: cleanup and readme
- **DOCKER**: Moved from Apache to Nginx [#106](https://github.com/reloxx13/TasmoAdmin/issues/106)
- **DOCKER**: Remove rm
  
## v0.0.7
- **FIX**: /app/tasmoadmin/.docker to disable SelfUpdate

## v0.0.6   
### 2018-05-23
- more rename
- hide selfupdate if on docker [#105](https://github.com/reloxx13/TasmoAdmin/issues/105)
- **FIX**: update on https [#103](https://github.com/reloxx13/TasmoAdmin/issues/103)
- provide TasmoAdmin Docker within repo [#92](https://github.com/reloxx13/TasmoAdmin/issues/92)
- add help menu [#100](https://github.com/reloxx13/TasmoAdmin/issues/100)

Big Thanks to @RaymondMouthaan for supporting the merge and renaming :)
   
## 2018-05-23
### SonWEB got renamed to TasmoAdmin!
   
### 2018-05-17
- add polish translation (thx Pawel)
- **FIX**: device list table header distance
- add BME680 Gas Sensor #98
   
### 2018-05-15
- **FIX**: autoscan for devices with more that 4 outputs #96
- support up to 5x DS18x20 #94
   
### 2018-05-06
- add distance sensor data (#89)
- **FIX**: overlapping sensor data on startpage (#90)
- **FIX**: device list some values did not get updated after timeout
- autoscan: remember fromIP and toIP (saved in MyConfig.php now) (#67)
   
### 2018-04-10
- dont hide device infos on error/timout in list
  - the switch will be displayed in red on error (but stay on last known state)
- add 8 sec refreshtime option to settings   
   
### 2018-04-06
- adjust background of sensor data in daymode
   
### 2018-03-30
- rollback to ajax status requests on device list
  - multicurl was timeouting alot :/
- **FIX**: skip minimal if no minimal firmware was uploaded
   
### 2018-03-25
- **FIX**: for some line-height
   
### 2018-03-23
- **FIX**: #83 (json changed for sensors)
- add BME280 sensor
- **FIX**:  #82, json friendlyname is now an array since 5.12.0h
- some startpage responsive css fixes for sensor data
- add 2x DS18x20 sensor
   
### 2018-03-03
- **FIX**: nightmode always
- startpage red border on error not shown when nightmode active **FIX**:
- some timeout adjustment
   
### 2018-02-27
- add coming HU firmware support for automode
- FR translation   
   
### 2018-02-22
- minor **FIX**: for startpage
   
### 2018-02-21
- **FIX**: uptime for safari   
- set timeout from 2 to 3 secs   
   
### 2018-02-19
- add NL translation
- updated spanish translation
   
### 2018-02-17
- change uptime format   
- add AutoScan
   - You can search for new Tasmota Devices between an IP Range now
   - Top Navi > Devices > AutoScan
   
### 2018-02-16
- HOTFIX for startpage
- **FIX**: missing name for devices without switches #55
   - sonoff bridge, sonoff sc, wemos8 etc
   - **you need to edit the device form the device list and set a name, the name input was missing before**
- you can set one position for a multi channel device now (all channels get the same position)
   - todo: add position per channel #48
- add HTU21 sensor
   
### 2018-02-15
- Optimize Status requests #49
- minor fixes
   
### 2018-02-11
- support runtime calculation from [reloxx13/Tasmota-Modified](https://github.com/reloxx13/Tasmota-Modified) for correct runtime with using sleep
   - Fix for [#1842](https://github.com/arendst/Tasmota/issues/1842)
- add BMP280 sensor
- add SI7021 sensor
   
### 2018-02-09
- add device position
   - you can edit this value by double click in the list or on the edit device page   
   - this is used to order the devices
- changed some backend csv handling
- load custom css file
   - you can now customize some css yourself if needed
   - create /resources/css/custom.css, the file gets loaded if exists
   - added a custom.default.css with the mobile breakpoints
   - **custom.default.css will be overwritten with a selfupdate. be sure to name your new file custom.css**
   
### 2018-02-09
- add support auto mode for new binaries CN, ES, FR, DS18X20(EN),
- remove brs from language file. tooltip will break by himself if text is too long
- use bootstrap tooltip
- some styling fixes
   
### 2018-02-08
- **FIX**: device update process
- some styling fixes
- add sensor SHT3X
   
### 2018-02-07
- add bootstrap and relayout
   - **if something looks wrong, pls clear your browser cache (Ctrl+r)**
   - fine tuning in next days ;)
- **FIX**: nightmode auto wont remove nightmode in the morning
   
### 2018-02-06
- minimal firmware is not a required field anymore
- add jquery ui tooltips
- show changelog on selfupdate page

### 2018-02-04
- some timing adjustments

### 2018-02-03
- higher timeout for curl, fixes some timeout problem with much devices   
- minor **FIX**: i18n  
- added double click edit of config in device list   
![2018-02-03_1](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/changelog/2018-02-03_1.PNG)   

### 2018-02-02
- add nightmode   
   - settings: auto (18h-8h), always on, disable   
- add userfirendly urls   
- **mod_rewrite is a requirement now, check ubuntu guide in the wiki**
- add devicename/id on device edit page   
- **FIX**: page title on device add/edit page   
- remove selfupdate warning (works great now)
- css **FIX**: for chrome
- mobile: **FIX**: action buttons linebreak

### 2018-02-01
- **FIX**: bug in selfupate
	- **PLEASE RUN SELFUPDATE TWICE**
- temp and humadity sensor are detected now
	- AM2301
	- DHT11
	- DS18B20
		- if you use another sensor, pls open an issue with the result from command 
		- http://DEVICEIP/cm?cmnd=status%2010
