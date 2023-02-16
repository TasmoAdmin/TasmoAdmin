# Upgrade

## From 2 -> 3

- Minimum PHP version bumped to 8.0 due to 7.4 becoming EOL.
- Routing moved internally requiring changes to your web server configuration .
  - Apache should be fine with the provided `.htaccess` but for nginx consult the docker nginx conf.
  - For containers that mount `/data` directly you will need to remove the `nginx` folder as we symlink these configurations to the host.
