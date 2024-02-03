# Upgrade

## From 3 -> 4

- Frontend asset changes - if you're using the zip or docker images no issues should occur
  - For folks building from this release you'll need to run `npm run build` instead of the `node minify`
  - Added bonus of `node_modules` and be removed for smaller backups

## From 2 -> 3

- Minimum PHP version bumped to 8.1 due to 7.4 becoming EOL.
- Routing moved internally requiring changes to your web server configuration .
  - Apache should be fine with the provided `.htaccess` but for nginx consult the docker nginx conf.
  - For containers that mount `/data` directly you will need to remove the `nginx` folder as we symlink these configurations to the host.
