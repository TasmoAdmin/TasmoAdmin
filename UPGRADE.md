# Upgrade

## Password Encryption Migration

- Device passwords in `devices.csv` are now encrypted at rest as `enc:v1:...`
- The first read after upgrading rewrites legacy plaintext password cells in place
- TasmoAdmin resolves the encryption key in this order:
  1. `_DATADIR_/.device-password.key`
  2. `TASMO_DEVICE_PASSWORD_KEY` as a base64-encoded 32-byte secret
  3. A newly generated key persisted to `.device-password.key`
- If both the sidecar file and environment variable exist, they must match exactly or TasmoAdmin returns a hard error until the mismatch is fixed

## From 4 -> 5

 - Minimum PHP version bumped to 8.2 due to 8.1 becoming EOL.


## From 3 -> 4

- Frontend asset changes - if you're using the zip or docker images no issues should occur
  - For folks building from this release you'll need to run `npm run build` instead of the `node minify`
  - Added bonus of `node_modules` and be removed for smaller backups

## From 2 -> 3

- Minimum PHP version bumped to 8.1 due to 7.4 becoming EOL.
- Routing moved internally requiring changes to your web server configuration .
  - Apache should be fine with the provided `.htaccess` but for nginx consult the docker nginx conf.
  - For containers that mount `/data` directly you will need to remove the `nginx` folder as we symlink these configurations to the host.
