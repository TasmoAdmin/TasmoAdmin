<div align="center">

<p><img width="150" src="./assets/logo.svg"/></p>

<h1>TasmoAdmin</h1>

[![Main](https://github.com/TasmoAdmin/TasmoAdmin/actions/workflows/main.yml/badge.svg)](https://github.com/TasmoAdmin/TasmoAdmin/actions/workflows/main.yml)
[![codecov](https://codecov.io/gh/TasmoAdmin/TasmoAdmin/branch/master/graph/badge.svg?token=8CWi1DIIjP)](https://codecov.io/gh/TasmoAdmin/TasmoAdmin)
[![Discord](https://img.shields.io/discord/401474444914196490)](https://discord.gg/gG2VDsSKWt)

[![GitHub release](https://badgen.net/github/release/TasmoAdmin/TasmoAdmin)](https://GitHub.com/TasmoAdmin/TasmoAdmin/releases/)
[![GitHub contributors](https://badgen.net/github/contributors/TasmoAdmin/TasmoAdmin)](https://GitHub.com/TasmoAdmin/TasmoAdmin/graphs/contributors/)
[![GitHub stars](https://badgen.net/github/stars/TasmoAdmin/TasmoAdmin)](https://github.com/TasmoAdmin/TasmoAdmin/stargazers)
[![GitHub forks](https://badgen.net/github/forks/TasmoAdmin/TasmoAdmin)](https://github.com/TasmoAdmin/TasmoAdmin/network)
[![Github all releases](https://badgen.net/github/dt/TasmoAdmin/TasmoAdmin)](https://GitHub.com/TasmoAdmin/TasmoAdmin/releases/)
[![GitHub license](https://badgen.net/github/license/TasmoAdmin/TasmoAdmin)](https://github.com/TasmoAdmin/TasmoAdmin/blob/master/LICENSE)
[![bootstrap](https://img.shields.io/badge/bootstrap-v5.3.x-%237952B3.svg)](https://getbootstrap.com/)
[![php](https://img.shields.io/badge/php-8.2%2B-%238892BF.svg)](https://www.php.net/)

</div>

TasmoAdmin (previously SonWEB) is an administrative platform for devices flashed with [Tasmota](https://github.com/arendst/Tasmota). It can run standalone, as a container, or as a Home Assistant addon.

## Features

* Login protected
* Multi update process
  * Select devices to update
  * Automatic mode downloads latest firmware bin from the Tasmota OTA site
* Show device information and sensor data
* Responsive Bootstrap 5 interface
  * SCSS-based assets with minified builds
* Configure devices from the web UI
* Self-update function for TasmoAdmin (disabled for Docker installs)
* Night mode with `Enable` / `Disable` / `Auto` settings
* Autoscan to find Tasmota devices
  * Network range scanning
  * MQTT broker discovery with configurable topic prefixes, subscriptions, and timeout
  * Device-specific MQTT topic matching for discovery results
* Optional touch-friendly toggle confirmations
  * Global default in settings
  * Per-device override
* Device list batch actions
  * Send commands, create backups, restart devices, and delete devices from the list view
* Startpage visibility controls
  * Hide selected devices from the startpage without removing them from the inventory
* Support for multiple sensors
* Encrypt stored device passwords at rest
* Fleet health monitoring
  * Background collector combines HTTP polling and MQTT (LWT/telemetry) into one state per device
  * Health page with state filters, search, per-channel status, signal strength and last seen
  * Health dots on the startpage and device list
* Passkey (WebAuthn) sign-in next to username and password
* Installable progressive web app with an offline fallback page
* Light and dark themes built on shared design tokens, with compact device cards on mobile

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

This is a Linux Alpine based image with Nginx and PHP 8.5 installed. It supports multiple architectures, **amd64** (i.e. Synology DSM), **arm** (i.e. Raspberry PI3) and  **arm64** (i.e. Pine64). Check out the [Guide for TasmoAdmin on Docker](https://github.com/reloxx13/TasmoAdmin/wiki/Guide-for-TasmoAdmin-on-Docker) for setup instructions.

This is the recommended way to get up and running.

### Home Assistant Addon

TasmoAdmin is also available as a [Home Assistant](https://www.home-assistant.io/) addon. See the [TasmoAdmin Home Assistant app](https://github.com/hassio-addons/app-tasmoadmin) for more information.

### Using a Web Server

TasmoAdmin should run on any webserver that supports PHP 8.2 or newer.

Check the [guides](https://github.com/TasmoAdmin/TasmoAdmin/wiki) on the Wiki for more information.

## Configuration

Some environment variables are configured to allow easier customisation of the application

- `TASMO_DATADIR` - Data directory, including a trailing slash. Defaults to `./tasmoadmin/data/`
- `TASMO_BASEURL` - Customise the base URL for the application
- `TASMO_TMPDIR` - Directory for temporary cache files, including a trailing slash. Defaults to `./tasmoadmin/tmp/`
- `TASMO_SESSIONDIR` - Directory for login sessions, including a trailing slash. Defaults to `sessions/` inside the data directory so logins survive container restarts
- `TASMO_HEALTHDIR` - Directory for the fleet health database, including a trailing slash. Defaults to `./tasmoadmin/health/`; the Docker image uses `/health/`
- `TASMO_DEBUG` - Set to `true` to display PHP errors. Disabled by default.
- `NO_AUTH` - Set to `true` to bypass the built-in login when authentication is handled externally
- `TASMO_DEVICE_PASSWORD_KEY` - Base64-encoded 32-byte secret for device password encryption at rest
- `TASMO_ALLOW_CROSS_SITE_IFRAME` - Set to `true` to embed TasmoAdmin in another HTTPS site, such as Home Assistant or Organizr; HTTP requests remain restricted

### Device Password Encryption

TasmoAdmin encrypts only the `password` column in `devices.csv`. Usernames, column order, and in-memory `Device` objects remain unchanged.

1. If `_DATADIR_/.device-password.key` exists, TasmoAdmin uses it.
2. Otherwise, if `TASMO_DEVICE_PASSWORD_KEY` exists, TasmoAdmin uses it.
3. Otherwise, TasmoAdmin lazily generates a 32-byte key, persists it to `.device-password.key`, and uses it.
4. If both sources exist, they must match exactly or TasmoAdmin fails closed.

Encrypted password cells are stored as `enc:v1:<base64(iv||tag||ciphertext)>`.

On the first read after upgrading, legacy plaintext password cells are migrated in place to the encrypted format. Running `clean=devices` removes both `devices.csv` and `.device-password.key` for file-backed installs.

### Session and CSRF protection

Authenticated state changes require a POST request with the session CSRF token. The session cookie uses `SameSite=Lax` by default, so an iframe deployment continues to work when the parent and TasmoAdmin are same-site. To embed TasmoAdmin in another HTTPS site, such as Home Assistant or Organizr, set `TASMO_ALLOW_CROSS_SITE_IFRAME=true`. This uses `SameSite=None; Secure` only for HTTPS requests; HTTP stays at `SameSite=Lax`. Browsers that block third-party cookies may still require a user exception.

For a deployment check, verify an authenticated device command and self-update form in both day and night mode, then confirm that a cross-site POST and a legacy state-changing GET URL leave the installation unchanged.

Login sessions last 30 days and are extended while TasmoAdmin is in use. The session id is regenerated on login. Behind a reverse proxy that terminates TLS, the session cookie is marked `Secure` when the proxy sends `X-Forwarded-Proto: https`.

### Passkeys

Signed-in users can register passkeys under `Settings -> Passkeys` and then use `Sign in with a passkey` on the login page. Browsers only offer passkeys in a secure context, so TasmoAdmin must be served over HTTPS (or from `localhost`). Passkeys are bound to the host name used when registering them. Public keys are stored in `passkeys.json` in the data directory; username and password login keeps working.

### Fleet Health

The Docker image runs a `health-collector` service next to PHP-FPM. It polls every device over HTTP (`Status 0`) and listens on the MQTT broker configured under `Settings -> MQTT discovery`, then stores one state per device in a SQLite database:

* `online` - HTTP and MQTT are up, or HTTP is up for a device that does not use MQTT
* `degraded_mqtt` / `degraded_http` - only one of the two channels answers
* `offline` - neither channel answered within the grace period
* `unknown` - the collector has not seen the device yet

The MQTT topic is taken from the device form or, when that is empty, read from the device's own `Status 0` reply. Devices with MQTT disabled are monitored over HTTP only and show MQTT as `Not monitored`.

The collector reads these keys from `MyConfig.json`:

* `health_enabled` - `1` to run the collector (default `1`)
* `health_http_poll_interval` - seconds between HTTP polls (default `60`, minimum `5`)
* `health_offline_grace` - seconds without an answer before a channel counts as down (default `180`)
* `health_mqtt_subscription` - MQTT subscription used to watch devices (default `#`)

Outside Docker, run `php tasmoadmin/bin/health-collector` as a long-running service and point `TASMO_HEALTHDIR` at a directory outside the web root that both the collector and the web server can write to. `health_data` returns the same data as JSON.

### Progressive Web App

TasmoAdmin serves a web app manifest (`manifest`) and a service worker (`service-worker`) from its base URL, so it can be installed from the browser menu, or from the `Install app` entry that appears in the navigation when the browser allows it. The service worker caches static assets and an offline page only; device state and commands always go to the network.

### MQTT Discovery

TasmoAdmin can discover devices through your MQTT broker in addition to classic network autoscan.

Configure the broker connection in `Settings -> MQTT discovery`, then use the MQTT tab in autoscan to:

* subscribe to one or more discovery topics such as `tele/+/LWT`
* match existing devices by MQTT topic and refresh their status
* add newly discovered devices from broker responses

You can also store a device-specific MQTT topic in the device edit form to make MQTT discovery matching more reliable in installations with custom topic layouts.

## Development

Provided is a docker-compose setup to ease getting started.

Simply run:

```bash
make dev
```

Then visit http://localhost:8000

Persistent storage within this setup is located in the `.storage` folder.

### DDEV

The repository also includes a DDEV setup for local development. It uses:

* `apache-fpm`
* PHP `8.5`
* Node.js `24`
* no database container

Start the environment with:

```bash
ddev start
```

Install PHP and Node.js dependencies:

```bash
ddev install-deps
```

Install the repository hooks for local staged-file validation:

```bash
pre-commit install
```

Build the frontend assets:

```bash
ddev build-assets
```

Then visit `https://tasmoadmin.ddev.site`.

Common development commands:

```bash
ddev ssh
ddev exec phpunit
ddev qa
ddev restart
ddev stop
```

Notes:

* `ddev install-deps` runs `composer install` and `npm ci` in `tasmoadmin/`
* `ddev build-assets` runs the frontend build
* `ddev qa` runs the Composer quality checks
* `pre-commit run --all-files` runs the repository hook set locally
* `ddev exec npm run test:js` runs the JavaScript test suite
* `ddev exec npm run prettier:check` verifies formatting for frontend files


## Translations

We use [Transifex][transifex] to maintain translations of this project. If you are not familiar with this service, you can read [Transifex Documentation][transifex-docs] to get started.


### Add or update translations

Here are steps to translate the extension to a specific language.

1. Join [our team][transifex-team] on Transifex.
2. Translate resources using Transifex web interface.

## Support

Use the issue functionality on this repo to report bugs or feature requests.

Alternatively, join the [Discord server](https://discord.gg/gG2VDsSKWt).

## Powered by

[![JetBrains logo.](https://resources.jetbrains.com/storage/products/company/brand/logos/jetbrains.svg)](https://jb.gg/OpenSourceSupport)

This project supported by JetBrains through their [Licenses for Open Source](https://www.jetbrains.com/community/opensource/) program.

[transifex-docs]: https://docs.transifex.com/getting-started-1/translators
[transifex-team]: https://explore.transifex.com/tasmoadmin/tasmoadmin/
[transifex]: https://www.transifex.com/
