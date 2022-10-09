# CHANGELOG

## UPCOMING

## PUBLISHED

### v2.1.0

#### Note

With the addition of the tasmota scraping changes the requirement for the `php-dom` extension is needed.

##### Noticeable changes

* Rewritten update logic and UI
* Use Tasmota for firmwares allowing beta firmware upgrades
* Support for PHP 8.1
* IP range scanning improvements
* Bug fixes

###### What's Changed

* Build unified changelog by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/583
* Correct changelog resolution by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/584
* Tidy Sonoff by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/586
* Leverage package manager for front-end assets by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/492
* Bump phpstan/phpstan from 1.7.15 to 1.8.0 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/588
* Bump js-cookie from 2.2.1 to 3.0.1 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/589
* Fix js-cookie bump by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/591
* Use npm jquery.i18n by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/592
* Bump jquery to 3.6.0 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/593
* Fix header dependencies for i18n by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/594
* Fix jQuery 3 migration by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/595
* Rewrite with fetch + check updated version by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/579
* Fix guzzle timeout values by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/596
* Update zh-TW localization by @petercpg in https://github.com/TasmoAdmin/TasmoAdmin/pull/597
* Fix local dev for firmware path by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/598
* Fix development nginx config by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/600
* Use env var instead of hardcoding for local-dev by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/599
* Fix prop selection for toggle by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/601
* Tidy device update js by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/602
* Sonoff js tidy by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/603
* Bump phpstan/phpstan from 1.8.0 to 1.8.1 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/604
* Tidy Sonoff js usage + fix es6 refactor by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/605
* Tidy js config by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/606
* Bump bootstrap from 4.6.1 to 4.6.2 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/608
* Bump phpstan/phpstan from 1.8.1 to 1.8.2 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/607
* Bump @fortawesome/fontawesome-free from 6.1.1 to 6.1.2 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/613
* Bump @node-minify/core from 6.2.0 to 6.4.0 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/614
* Bump mikey179/vfsstream from 1.6.10 to 1.6.11 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/611
* Bump symfony/filesystem from 5.4.9 to 5.4.11 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/610
* Bump @node-minify/terser from 6.2.0 to 6.4.0 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/612
* Fix JS refactor by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/615
* Tidy URLHelper by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/617
* Pass base URL into UrlHelper by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/618
* Add whoops error handler by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/619
* Add css minification by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/620
* Add firmware checking config by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/621
* Catch rate limit on GitHub fetch by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/622
* Fix config for unset by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/623
* Fix write changes on device actions + tidy device config by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/627
* Bump @node-minify/core from 6.4.0 to 7.0.0 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/625
* Bump @node-minify/terser from 6.4.0 to 7.0.0 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/626
* Use node 16.16.0 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/628
* Fix device update with retries by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/629
* Fix FE check by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/634
* Translations + base URL by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/635
* Bump jquery from 3.6.0 to 3.6.1 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/640
* Bump composer deps by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/641
* Use Ota URL for firmware download by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/630
* Fix for PHP 8.1 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/643
* Fix changelog by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/644
* Upgrade to PHP 8.1 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/642
* Fix target version for non-auto path by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/646
* Fix version for dev by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/647
* Prevent same upgrade by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/645
* Optmise composer for production by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/648
* Bump phpstan/phpstan from 1.8.2 to 1.8.4 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/650
* Bump phpunit/phpunit from 9.5.23 to 9.5.24 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/649
* Fix start click by @inverse in https://github.com/TasmoAdmin/Tas## v2.0.0moAdmin/pull/651
* Generate .version on release by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/652
* Fix version write by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/654
* Fix URLHelper for min by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/655
* Fix force upgrade flag by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/656
* Remove legacy CSS/HTML debug by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/657
* Only enable whoops when TASMO_DEBUG env var set by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/659
* Add codecov by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/660
* Coverage badge by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/661
* Bump phpstan/phpstan from 1.8.4 to 1.8.5 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/662
* Catch guzzle errors in tasotahelper by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/664
* Bump phpunit/phpunit from 9.5.24 to 9.5.25 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/665
* Bump phpstan/phpstan from 1.8.5 to 1.8.6 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/666
* Improve tests for IpHelper by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/668
* Bump docker/login-action from 1 to 2 by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/670
* Bump actions/checkout from 1 to 3 by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/671
* Bump symfony/filesystem from 5.4.12 to 5.4.13 in /tasmoadmin by @dependabot in https://github.com/TasmoAdmin/TasmoAdmin/pull/669
* Fix IP range logic by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/674
* Improve memory efficency of IP generation by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/675
* IpHelper without range by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/676
* Add php-cs-fixer by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/677

### v2.0.0

#### Breaking Changes

* The minimum PHP version has been set to 7.4

#### What's Changed

* Add mbstring dependency to docker setup by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/490
* Tidy UrlHelper by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/493
* Add JetBrains support notice by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/494
* Use PSR-4 Autoloading by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/495
* Fix import with Sonoff integration by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/496
* Improve developer flow by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/497
* Use consistent naming + document by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/498
* Tidy docker setup by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/499
* Add beta workflow by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/500
* Fix JSON language dump by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/502
* Fix Sonoff by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/503
* Fix selfupdate.php by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/504
* Fix release pipeline permissions by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/505
* Fix write permissions for release by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/506
* handle error state sonoff by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/507
* Add composer install to package by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/508
* Dev images by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/509
* Fix dev docker push by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/510
* Drop test from release by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/512
* Downgrade Alpine to from 3.15 -> 3.13 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/511
* Tidy pipelines by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/513
* Downgrade qemu to v2.12.0 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/515
* Fix selfupdate import by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/517
* Rework device layer + add tests by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/518
* Fix up DeviceRepository setup by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/520
* Setup PHPStan by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/521
* Config add basic test by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/522
* Bump alpine + qemu versions back by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/516
* Rework upload form by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/523
* Improve DeviceRepository by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/526
* Device rework by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/527
* Automate minification by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/528
* Fix Device refactor by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/530
* Extract IP generation by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/529
* Handle reload on device update by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/531
* Remove dev deps and optmise autoloader on package by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/533
* Refactor update work by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/532
* Revert "Refactor update work" by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/534
* Fix placeholder translations by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/536
* Fix access to props by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/537
* Fix device action for device refactor by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/538
* Sonoff work by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/539
* Refactor update work by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/535
* Fix devices.php for device refactor by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/540
* Improve Sonoff.php by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/541
* Improve Sonoff by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/542
* New issue templates by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/544
* Sonoff simplification by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/545
* Add missing CLOSE translation by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/546
* Tidy release fetching by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/547
* Helper for getting Tasmoadmin release notes by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/548
* Upload cleanup by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/549
* Update backend check firmware accessible by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/550
* Fix update message by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/551
* move request out of Sonoff by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/553
* Add composer.lock by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/556
* Include composer.lock in gitignore by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/557
* Nighttime tidy by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/558
* Fix urlencode by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/559
* Drop unused imports from pages by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/560
* Improve version comparison in update check by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/561
* Tidy error reporting by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/562
* Bump docker setup to PHP 8 by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/563
* Fix device actions by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/564
* Prevent docker release on fork by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/565
* Fix missing translation for name adoption by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/566
* Fix auto gzip by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/567
* Fix OTA URL by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/568
* Tidy bootstrap by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/571
* Fix bootstrap autoload for clean by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/572
* Correct correct exception when checking firmware by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/575
* Bump composer dependencies by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/576
* Add additional FirmwareChecker test by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/577
* Remove legacy autoload from bootstrap by @inverse in https://github.com/TasmoAdmin/TasmoAdmin/pull/578

### v1.8.0

- Update lang_pl.ini by @ponuryzrzeda in [#409](https://github.com/TasmoAdmin/TasmoAdmin/issues/#409)
- Missing translations, typos and better French by @amayii0 in [#421](https://github.com/TasmoAdmin/TasmoAdmin/issues/#421)
- Improve German translation by @CodeFinder2 in [#422](https://github.com/TasmoAdmin/TasmoAdmin/issues/#422)
- Remove clear_env from docker php config by @merlinschumacher in [#416](https://github.com/TasmoAdmin/TasmoAdmin/issues/#416)
- Update lang_it.ini by @ZioCook in [#436](https://github.com/TasmoAdmin/TasmoAdmin/issues/#436)
- Gzip support by @PhilipWhiteside in [#447](https://github.com/TasmoAdmin/TasmoAdmin/issues/#447)
- Migrate to GitHub Actions by @inverse in [#451](https://github.com/TasmoAdmin/TasmoAdmin/issues/#451)
- Correct GitHub spelling by @inverse in [#457](https://github.com/TasmoAdmin/TasmoAdmin/issues/#457)
- Improve gzip output by @inverse in [#460](https://github.com/TasmoAdmin/TasmoAdmin/issues/#460)
- Fix loading versions by passing user-agent by @inverse in [#458](https://github.com/TasmoAdmin/TasmoAdmin/issues/#458)
- make sure vsprintf() is called with array by @martin-herzog in [#461](https://github.com/TasmoAdmin/TasmoAdmin/issues/#461)
- Bigcookie german lang update for gzip support by @bigcookie in [#464](https://github.com/TasmoAdmin/TasmoAdmin/issues/#464)
- Add foundations for GitHub actions release pipeline by @inverse in [#452](https://github.com/TasmoAdmin/TasmoAdmin/issues/#452)

### v1.7.0
- FIX: Select all and filter were conflicting, fixes [#400](https://github.com/TasmoAdmin/TasmoAdmin/issues/400)
- FIX: Select all style position
- FIX: Wrong var used in try to fix the json response fixes [#345](https://github.com/TasmoAdmin/TasmoAdmin/issues/345)
- **NEW**: Adds a filter for the device list view thanks to @[alexhk](https://github.com/alexhk) [PR#399](https://github.com/TasmoAdmin/TasmoAdmin/pull/399)
    - Search for Name/Hostname, IP#123, ID#321, POS#1, Single/Multi
    - Hide the search filter in settings
- FIX: Correct bad JSON Response from Tasmota 8.5.0.x, fixes [#398](https://github.com/TasmoAdmin/TasmoAdmin/issues/345),[#397](https://github.com/TasmoAdmin/TasmoAdmin/issues/397),[#394](https://github.com/TasmoAdmin/TasmoAdmin/issues/394)   

### v1.6.5
- NEW: Add support for BME280 SeaPressure thanks to @[gknauf](https://github.com/gknauf) [#283](https://github.com/TasmoAdmin/TasmoAdmin/issues/283)  
- *FIX*: Possible fix for config crashes      

###  v1.6.4
- **NEW**: Exclude devices from "ALL OFF" [#312](https://github.com/TasmoAdmin/TasmoAdmin/issues/312)
- **NEW**: Protect device to get powered on or/and off
    - For both you need to edit the device and set the desired checkboxes
    - At the bottom of the table view is a button to unlock the protected device for 60s
    - On the startpage you can change the state if you press the device button for 5s   
    ![grafik](https://user-images.githubusercontent.com/14855001/89078631-0cf95780-d385-11ea-9787-cce69adfe870.png)
- UPDATE: Add 120s and 300s to refresh times
- **UPDATE**: (non-docker) Selfupdate switch between beta and stable
- UPDATE: Get live list of available tasmota firmware versions for automatic update
- UPDATE: Add version selectbox on device update page
- UPDATE: Autofocus on login and forms [#370](https://github.com/TasmoAdmin/TasmoAdmin/issues/370)
- NEW: Add support for AHT1X sensor thanks to @[crashdown79](https://github.com/crashdown79) [#356](https://github.com/TasmoAdmin/TasmoAdmin/issues/356)
- UPDATE: Add new password hashing thanks to @[inverse](https://github.com/inverse) ( [PR#357](https://github.com/TasmoAdmin/TasmoAdmin/pull/357) )
- UPDATE: Hide password in device autoscanner thanks to @[WatskeBart](https://github.com/WatskeBart) ( [PR#387](https://github.com/TasmoAdmin/TasmoAdmin/pull/387) )
    - and add show/hide password button
- NEW: Add zh-TW localization thanks to @[petercpg](https://github.com/petercpg) ( [PR#374](https://github.com/TasmoAdmin/TasmoAdmin/pull/374) )  
- NEW: Add a ENV variable ( TASMO_BASEURL ) to adjust the base url thanks to @[rhuss](https://github.com/rhuss) ( [PR#346](https://github.com/TasmoAdmin/TasmoAdmin/pull/346) )   
- UPDATE: Font Awesome 5.11.2 -> 5.14.0   
- UPDATE: Bootstrap 4.3.1 -> 4.5.0
- UPDATE: Change wiki links (top navi) to tasmota docs [#381](https://github.com/TasmoAdmin/TasmoAdmin/issues/381)
- UPDATE: Change to new changelog/releaselogs (device update page)
- UPDATE: Updated polish translation, thx @[WiktorBuczko](https://github.com/WiktorBuczko) and @[pepeEL](https://github.com/pepeEL)
- UPDATE: Support up to 8 DS18B20 [#333](https://github.com/TasmoAdmin/TasmoAdmin/issues/333) 
- UPDATE: Another adjust device icon sizes on home screen [#325](https://github.com/TasmoAdmin/TasmoAdmin/issues/325)
- **UPDATE**: IOCage Update and FreeNAS Plugin Support, thx @[tprelog](https://github.com/tprelog)
- UPDATE: Adjust device icon sizes on home screen [#325](https://github.com/TasmoAdmin/TasmoAdmin/issues/325)

### v1.6.3
- **HOTFIX** DOCKER: fix and prevent nginx.conf link loop

### v1.6.2
- **FIX**: Support for Tasmota 7.1.1.1 [#326](https://github.com/TasmoAdmin/TasmoAdmin/issues/326) 
  - Module data could not get parsed cause of changes in JSON from newer Tasmota Version
- **UPDATE**: Support for MAX31855 Sensor [#327](https://github.com/TasmoAdmin/TasmoAdmin/issues/327)
- **UPDATE**: Added sensor icon for none-relais devices
- FIX: Displaying changelog and releaselog from tasmota correctly for new MarkDown format
- FIX: Try to fix invalid json by ```nan``` by replacing it with ```"NaN"``` [#318](https://github.com/TasmoAdmin/TasmoAdmin/issues/318)


### v1.6.1
- **UPDATE**: Support for LM75AD Sensor [#275](https://github.com/TasmoAdmin/TasmoAdmin/issues/275)
- **FIX**: Follow up for renaming of sonoff -> tasmota [#310](https://github.com/TasmoAdmin/TasmoAdmin/issues/310) [PR#311](https://github.com/TasmoAdmin/TasmoAdmin/pull/311)
- **UPDATE**: Font Awesome 5.3.1 -> 5.11.2   
- **UPDATE**: Bootstrap 4.1.2 -> 4.3.1
-FIX: login box position fix
- **UPDATE**: Change to new Tasmota Github URL
- FIX: loop 3rd an 4th ip block in autoscan to support bigger networks [#302](https://github.com/TasmoAdmin/TasmoAdmin/issues/302), thx @[Leuselator](https://github.com/Leuselator) 
  - Note: Autoscan will take longer now
- **UPDATE**: Change htaccess to new syntax [#299](https://github.com/TasmoAdmin/TasmoAdmin/issues/299), thx @[joba-1](https://github.com/joba-1)
- FIX: Remove PHP5 support, add SELinux info to readme [#290](https://github.com/TasmoAdmin/TasmoAdmin/issues/290)
- FIX: Remove SSL Keys 
- **UPDATE**: Support multi BMP´s ([pr#4195](https://github.com/arendst/Tasmota/pull/4195))
  - BME280-76/77
- adjust some colors  
- **FIX**: IOS/Mobile Scroll bug [#244](https://github.com/TasmoAdmin/TasmoAdmin/issues/244) 
- **FIX**: Startpage Devices without Relais shaking and breaking js [#97](https://github.com/TasmoAdmin/TasmoAdmin/issues/97)  

    
### v1.6.0
- FIX: startpage all off icon error         
- **UPDATE**: Support Sensor BMP180 [#224](https://github.com/TasmoAdmin/TasmoAdmin/issues/224)
- FIX: login page horizontal scrollable removed
- FIX: JS bug caused devices not to load (non-docker only bug) #225 #226
- DEV: CSS & HTML fixes
- **NEW**: support ssl / https added [#113](https://github.com/TasmoAdmin/TasmoAdmin/issues/113) 
  
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
            
- **FIX**: Detection of StateText change [#199](https://github.com/TasmoAdmin/TasmoAdmin/issues/199), [#154](https://github.com/TasmoAdmin/TasmoAdmin/issues/154)   
- CI skipped, use v1.6.0-beta8
- **FIX**: XAMPP Folder Structure in ZIP
- **DEV**: Lower zip splitted parts filesize for git
- **FIX**: avoid sending backlog commands twice cuz backlog does not give any response. [#210](https://github.com/TasmoAdmin/TasmoAdmin/issues/210)
- **FIX**: Unwanted underscore added [#210](https://github.com/TasmoAdmin/TasmoAdmin/issues/210)
- **FIX**: XAMPP Pack fixed and updated to XAMPP 7.2.9.0 (PHP7) [141](https://github.com/TasmoAdmin/TasmoAdmin/issues/141), [209](https://github.com/TasmoAdmin/TasmoAdmin/issues/209)

- **FIX**: nginx config
- **CHANGE**:  HTTP/HTTPS Detection [#198](https://github.com/TasmoAdmin/TasmoAdmin/issues/198)
   - New Checkbox to choose http/https, default is http.   
![grafik](https://user-images.githubusercontent.com/14855001/45046243-e2f26a00-b075-11e8-9304-8621cc6e0ba5.png)
- **UPDATE**: Support Sensor DS18B20 [#202](https://github.com/TasmoAdmin/TasmoAdmin/issues/202)
- DEV: Add robots/search crawler protection
- **FIX**: Add device password field as password not text input [#184](https://github.com/TasmoAdmin/TasmoAdmin/issues/184)
- **FIX**: for new tasmota changelog structure [tasmota-commit#ace6180](https://github.com/arendst/Tasmota/commit/ace6180e67a29926fade72ab10015c18b07c973e)    
- **NEW**: CZ language ( Big thanks @Vladimir S. by Mail) 
- **FIX**: Update Tasmota language Releases for auto updates.    
- **UPDATE**: Font Awesome 5.0.13 -> 5.3.1   
- **UPDATE**: Bootstrap 4.1.1 -> 4.1.2
- **CHANGE**: Startpage rework:
![startpage_v1.2.PNG](https://raw.githubusercontent.com/reloxx13/reloxx13.github.io/master/media/tasmoadmin/changelog/startpage_v1.2.PNG)   
- **DEV**: Some more Error Catchings and debugs on error for lost configs [#207](https://github.com/TasmoAdmin/TasmoAdmin/issues/207)
- **NEW**: Detection of StateText change [#199](https://github.com/TasmoAdmin/TasmoAdmin/issues/199), [#154](https://github.com/TasmoAdmin/TasmoAdmin/issues/154) 

## v1.5.4
- **FIX** autoload case-sensitive [#182](https://github.com/TasmoAdmin/TasmoAdmin/issues/182)  
   
## v1.5.3
- **FIX** another fix for chrome ERR_RESPONSE_HEADERS_TOO_BIG [#182](https://github.com/TasmoAdmin/TasmoAdmin/issues/182)
   
   
## v1.5.2
- **FIX** Chrome crying ERR_RESPONSE_HEADERS_TOO_BIG [#182](https://github.com/TasmoAdmin/TasmoAdmin/issues/182)

## v1.5.1
- **FIX** Update Tag info [#180](https://github.com/TasmoAdmin/TasmoAdmin/issues/180)


## v1.5.0
- **NEW**: Link Tasmota Changelog Issues [#173](https://github.com/TasmoAdmin/TasmoAdmin/issues/173)
- **NEW**: Use json file for for config data
- **NEW**: Config is now saved in a cookie
  - Cookies are stored in the Clients Browser, reduces Reads on Servers Drive. 
  - Some uneccessery writes removed, boom.
  - Note: Encrypted User PW will not be saved in the Cookie.   
  Old Config File will be migrated to json and removed in a later release. Stays as Backup.   
  If you delete the MyConfig.json, it will generated new based on the MyConfig.php
- **DEV**: Use SCSS and Minified CSS/JS Resources, gives some performence boost and save network traffic.
- **NEW**: Get Current Tag from Docker [#176](https://github.com/TasmoAdmin/TasmoAdmin/issues/176)  
- **FIX**: $_SERVER REQUEST_SCHEME is not reliable [#174](https://github.com/TasmoAdmin/TasmoAdmin/issues/174)    
   
## v1.4.0
- **FIX**: set session dir inside tasmota dir instead of server root /tmp [#169](https://github.com/TasmoAdmin/TasmoAdmin/issues/169)
- **FIX**: go to selected homepage after login [#115](https://github.com/TasmoAdmin/TasmoAdmin/issues/115) 
- **NEW**: Disable update check option [#156](https://github.com/TasmoAdmin/TasmoAdmin/issues/156) 
- **NEW**: Add top scrollbar on device list [#170](https://github.com/TasmoAdmin/TasmoAdmin/issues/170) 
- MINOR: Rename "Show More" => "Detail View" in i18n.EN [#168](https://github.com/TasmoAdmin/TasmoAdmin/issues/168) 
- **NEW**: Support Multi Friendlyname on Device General Config Page (fw >= 5.12.0h) [Tasmota#3161](https://github.com/arendst/Tasmota/issues/3161)
- **FIX**: device config allow more steps for Sleep [#172](https://github.com/TasmoAdmin/TasmoAdmin/issues/172)      


## v1.3.3 
- MINOR: fix for travis building   
   
## v1.3.2   
- **FIX**: fix device update, use ota magic [#165](https://github.com/TasmoAdmin/TasmoAdmin/issues/165) [#166](https://github.com/TasmoAdmin/TasmoAdmin/issues/166) 

## v1.3.1   
- **NEW**: MQTT Config Tab on device config page [#101](https://github.com/TasmoAdmin/TasmoAdmin/issues/101)
  - ENHANCEMENT: Only changed config values will be send to the device
- MINOR: style show more btn responsive [#145](https://github.com/TasmoAdmin/TasmoAdmin/issues/145)


## v1.2.1
**HOTFIX**: Fix edit device #147

## v1.2.0
- **FIX**: some Tasmota 5.10.0 json #131 [hassio-addon#6](https://github.com/hassio-addons/addon-tasmoadmin/issues/6)
- **UPDATE**: Font Awesome 5.0.6 -> 5.1.0
- **DOCKER**: Show Changelog Link in helpmenu [#108](https://github.com/TasmoAdmin/TasmoAdmin/issues/108)
- MINOR: make footer smaller and sticky, darker link color in nightmode
  - top navi made a lil bit smaller, too
- MINOR: catch and remove control chars in json response [#78](https://github.com/TasmoAdmin/TasmoAdmin/issues/78)
- **NEW**: Show new update available icon in footer
- MINOR: Possible performence fixes for checkNightmode
- MINOR: cache resources by release tag version

## v1.1.0                              
- **NEW**: you can now set a default startpage [#115](https://github.com/TasmoAdmin/TasmoAdmin/issues/115)
- **FIX**: Multichannel devices don't restore correctly if backend fails temporarily #121          
- MINOR: dont be blind on page reload: set nightmode class initial if nightmode is enabled


## v1.0.7               
- **NEW**: add send command within device list [#100](https://github.com/TasmoAdmin/TasmoAdmin/issues/100)
- MINOR: error handling for selfupdate if travis build failed [#142](https://github.com/TasmoAdmin/TasmoAdmin/issues/142)       
- MINOR: restyle show more

## v1.0.6
- **DOCKER**: update busybox to fix travis build

## v1.0.4 - 1.0.5
- **FIX**: date in update [#124](https://github.com/TasmoAdmin/TasmoAdmin/issues/124)
- MINOR: change filename to load js file correctly (only firefox issue?)
  - fixes the show more checkbox 
- **NEW**: add configurable server port [#122](https://github.com/TasmoAdmin/TasmoAdmin/issues/122),[#131](https://github.com/TasmoAdmin/TasmoAdmin/issues/131)
- MINOR: show some energy data
- **NEW**: add iocage support by @tprelog :)

## v0.0.8 - v 1.0.3
- MINOR: add logo
- **UPDATE**: Font Awesome 5.0.6 -> 5.0.13
- **UPDATE**: bootstrap 4.0.0 -> 4.1.1
- **DOCKER**: clean-up unnecessary lines to reduce image size
- **DOCKER**: cleanup and readme
- **DOCKER**: Moved from Apache to Nginx [#106](https://github.com/TasmoAdmin/TasmoAdmin/issues/106)
- **DOCKER**: Remove rm
  
## v0.0.7
- **FIX**: /app/tasmoadmin/.docker to disable SelfUpdate

## v0.0.6   
### 2018-05-23
- more rename
- hide selfupdate if on docker [#105](https://github.com/TasmoAdmin/TasmoAdmin/issues/105)
- **FIX**: update on https [#103](https://github.com/TasmoAdmin/TasmoAdmin/issues/103)
- provide TasmoAdmin Docker within repo [#92](https://github.com/TasmoAdmin/TasmoAdmin/issues/92)
- add help menu [#100](https://github.com/TasmoAdmin/TasmoAdmin/issues/100)

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
