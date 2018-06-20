# CHANGELOG   


## Upcoming
- fix date in update #124
- change filename to load js file correctly (only firefox issue?)
  - fixes the show more checkbox 
- add configurable server port #122 #131
- show some energy data
- add iocage support by @tprelog :)

## v0.0.8
- add logo
- update font-awesome 5.0.6 -> 5.0.13
- update bootstrap 4.0.0 -> 4.1.1
  
## v0.0.7
- fixed /app/tasmoadmin/.docker to disable SelfUpdate

## v0.0.6   
### 2018-05-23
- more rename
- hide selfupdate if on docker #105
- fix update on https #103
- provide TasmoAdmin Docker within repo #92
- add help menu (#100)

Big Thanks to @RaymondMouthaan for supporting the merge and renaming :)
   
## 2018-05-23
### SonWEB got renamed to TasmoAdmin!
   
### 2018-05-17
- add polish translation (thx Pawel)
- fix device list table header distance
- add BME680 Gas Sensor #98
   
### 2018-05-15
- fix autoscan for devices with more that 4 outputs #96
- support up to 5x DS18x20 #94
   
### 2018-05-06
- add distance sensor data (#89)
- fix overlapping sensor data on startpage (#90)
- fix device list some values did not get updated after timeout
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
- fix skip minimal if no minimal firmware was uploaded
   
### 2018-03-25
- fix for some line-height
   
### 2018-03-23
- fix #83 (json changed for sensors)
- add BME280 sensor
- fix  #82, json friendlyname is now an array since 5.12.0h
- some startpage responsive css fixes for sensor data
- add 2x DS18x20 sensor
   
### 2018-03-03
- fix nightmode always
- startpage red border on error not shown when nightmode active fix
- some timeout adjustment
   
### 2018-02-27
- add coming HU firmware support for automode
- FR translation   
   
### 2018-02-22
- minor fix for startpage
   
### 2018-02-21
- fix uptime for safari   
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
- fix missing name for devices without switches #55
   - sonoff bridge, sonoff sc, wemos8 etc
   - **you need to edit the device form the device list and set a name, the name input was missing before**
- you can set one position for a multi channel device now (all channels get the same position)
   - todo: add position per channel #48
- add HTU21 sensor
   
### 2018-02-15
- Optimize Status requests #49
- minor fixes
   
### 2018-02-11
- support runtime calculation from [reloxx13/Sonoff-Tasmota-Modified](https://github.com/reloxx13/Sonoff-Tasmota-Modified) for correct runtime with using sleep
   - Fix for [#1842](https://github.com/arendst/Sonoff-Tasmota/issues/1842)
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
- fix device update process
- some styling fixes
- add sensor SHT3X
   
### 2018-02-07
- add bootstrap and relayout
   - **if something looks wrong, pls clear your browser cache (Ctrl+r)**
   - fine tuning in next days ;)
- fix nightmode auto wont remove nightmode in the morning
   
### 2018-02-06
- minimal firmware is not a required field anymore
- add jquery ui tooltips
- show changelog on selfupdate page

### 2018-02-04
- some timing adjustments

### 2018-02-03
- higher timeout for curl, fixes some timeout problem with much devices   
- minor fix i18n  
- added double click edit of config in device list   
![2018-02-03_1](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/changelog/2018-02-03_1.PNG)   

### 2018-02-02
- add nightmode   
   - settings: auto (18h-8h), always on, disable   
- add userfirendly urls   
- **mod_rewrite is a requirement now, check ubuntu guide in the wiki**
- add devicename/id on device edit page   
- fix page title on device add/edit page   
- remove selfupdate warning (works great now)
- css fix for chrome
- mobile: fix action buttons linebreak

### 2018-02-01
- fix bug in selfupate
	- **PLEASE RUN SELFUPDATE TWICE**
- temp and humadity sensor are detected now
	- AM2301
	- DHT11
	- DS18B20
		- if you use another sensor, pls open an issue with the result from command 
		- http://DEVICEIP/cm?cmnd=status%2010
