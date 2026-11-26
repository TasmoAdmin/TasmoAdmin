# Upgrade

## Password Encryption Migration

- Device passwords in `devices.csv` are now encrypted at rest as `enc:v1:...`
- The first read after upgrading rewrites legacy plaintext password cells in place
- Before the first encryption rewrite, TasmoAdmin copies the plaintext CSV to `devices.csv.pre-encryption.bak` next to `devices.csv`. Keep or delete this file consciously - it contains plaintext passwords.
- TasmoAdmin resolves the encryption key in this order:
  1. `_DATADIR_/.device-password.key`
  2. `TASMO_DEVICE_PASSWORD_KEY` as a base64-encoded 32-byte secret
  3. A newly generated key persisted to `.device-password.key`
- If both the sidecar file and environment variable exist, they must match exactly or TasmoAdmin returns a hard error until the mismatch is fixed
- A malformed value in one key source no longer blocks a valid key in the other: the valid sidecar or environment key wins, and malformed values only hard-error when no usable key exists

### Downgrading to a version before 5.1.0

Older releases read the password cell verbatim and cannot decrypt `enc:v1:...` values. After a downgrade, every password-protected device silently fails authentication. Before downgrading, restore the plaintext backup:

```
cp devices.csv.pre-encryption.bak devices.csv
```

### Losing or rotating the encryption key

Passwords encrypted with a lost key cannot be recovered. TasmoAdmin shows a hard error with recovery instructions. To recover:

1. Restore the original key if you have a backup of `.device-password.key` or know the `TASMO_DEVICE_PASSWORD_KEY` value.
2. Otherwise remove all `enc:v1:...` password values in `devices.csv` (replace the cell with an empty value) and any `enc:v1:...` value in `MyConfig.json`, delete the corrupt key file, and re-enter the passwords in the UI. A fresh key is generated automatically.

When migrating an installation to another host, copy `.device-password.key` along with `devices.csv` and `MyConfig.json`, or set `TASMO_DEVICE_PASSWORD_KEY` on the target.

## HTTP API changes

- All `POST` requests now require a valid `csrf_token` parameter; requests without one are rejected with `403`.
- Legacy `GET` mutation endpoints (`device_action` delete, `actions` with mutation query params, `selfupdate`, `upload` auto) now return `405` - use the `POST` forms instead.
- `logout` and `change_language` are `POST`-only routes.

If you scripted against TasmoAdmin endpoints, those scripts need updating.

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
