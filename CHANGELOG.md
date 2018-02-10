# CHANGELOG   
   
   
   
## 2018-02-09
- add device position
   - you can edit this value by double click in the list or on the edit device page   
   - this is used to order the devices
- changed some backend csv handling
- load custom css file
   - you can now customize some css yourself if needed
   - create /resources/css/custom.css, the file gets loaded if exists
   - added a custom.default.css with the mobile breakpoints
   - **custom.default.css will be overwritten with a selfupdate. be sure to name your new file custom.css**
   
## 2018-02-09
- add support auto mode for new binaries CN, ES, FR, DS18X20(EN),
- remove brs from language file. tooltip will break by himself if text is too long
- use bootstrap tooltip
- some styling fixes
   
## 2018-02-08
- fix device update process
- some styling fixes
- add sensor SHT3X
   
## 2018-02-07
- add bootstrap and relayout
   - **if something looks wrong, pls clear your browser cache (Ctrl+r)**
   - fine tuning in next days ;)
- fix nightmode auto wont remove nightmode in the morning
   
## 2018-02-06
- minimal firmware is not a required field anymore
- add jquery ui tooltips
- show changelog on selfupdate page

## 2018-02-04
- some timing adjustments

## 2018-02-03
- higher timeout for curl, fixes some timeout problem with much devices   
- minor fix i18n  
- added double click edit of config in device list   
![2018-02-03_1](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/sonweb/changelog/2018-02-03_1.PNG)   

## 2018-02-02
- add nightmode   
   - settings: auto (18h-8h), always on, disable   
- add userfirendly urls   
- **mod_rewrite is a requirement now, check ubuntu guide in the wiki**
- add devicename/id on device edit page   
- fix page title on device add/edit page   
- remove selfupdate warning (works great now)
- css fix for chrome
- mobile: fix action buttons linebreak

## 2018-02-01
- fix bug in selfupate
	- **PLEASE RUN SELFUPDATE TWICE**
- temp and humadity sensor are detected now
	- AM2301
	- DHT11
	- DS18B20
		- if you use another sensor, pls open an issue with the result from command 
		- http://DEVICEIP/cm?cmnd=status%2010
