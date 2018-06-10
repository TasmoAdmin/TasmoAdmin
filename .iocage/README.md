# iocage-tasmoadmin

This should help create an iocage based jail for TasmoAdmin using ngnix and php72 (tested on FreeNAS-11.1-U5)

**One time only:**

iocage should be activated. `iocage activate` is basically just telling iocage which zpool to use. You'll also want to fetch a base release.
    
  For example to use zpool named " tank "

    sudo iocage activate tank
    sudo iocage fetch

More information about setting up and using iocage can be found [HERE](https://iocage.readthedocs.io/en/latest/basic-use.html)

---


Create a jail using a pkg-list to install requirements

	wget https://raw.githubusercontent.com/tprelog/TasmoAdmin/iocage-plugin/.iocage/tasmoadmin-pkgs.json
	sudo iocage create -r 11.1-RELEASE -n tasmoadmin boot=on dhcp=on bpf=yes vnet=on -p tasmoadmin-pkgs.json


Download TasmoAdmin and get it running with nginx

	sudo iocage exec tasmoadmin git clone https://github.com/tprelog/TasmoAdmin.git /root/TasmoAdmin
	sudo iocage exec tasmoadmin bash /root/TasmoAdmin/.iocage/install_tasmoadmin.sh

You should now be able to use TasmoAdmin by entering `http://YOUR.TASMOADMIN.IP.ADDRESS` in your browser

---

To see a list of jails as well as their ip address

    sudo iocage list -l
    
    +-----+------------+------+-------+------+------------------+---------------------+-----+----------+
    | JID |    NAME    | BOOT | STATE | TYPE |     RELEASE      |         IP4         | IP6 | TEMPLATE |
    +=====+============+======+=======+======+==================+=====================+=====+==========+
    | 1   | tasmoadmin | on   | up    | jail | 11.1-RELEASE-p10 | epair0b|192.0.1.126 | -   | -        |
    +-----+------------+------+-------+------+------------------+---------------------+-----+----------+
 
