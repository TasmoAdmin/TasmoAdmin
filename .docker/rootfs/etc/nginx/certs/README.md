# Create the Certificate using OpenSSL
openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout tasmoadmin.key -out tasmoadmin.crt -config ta
smoadmin.conf
