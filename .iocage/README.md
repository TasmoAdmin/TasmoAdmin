# iocage-tasmoadmin

This should help create an iocage based jail for TasmoAdmin using nginx and php72 (Tested on FreeNAS-11.2-BETA3)

These steps were tested on FreeNAS and assume you have already [activated iocage and fetched the 11.2-RELEASE](https://iocage.readthedocs.io/en/latest/basic-use.html#activate-iocage.)

More information about iocage on FreeNAS can be found in the [FreeNAS guide](https://doc.freenas.org/11.2/jails.html#using-iocage)

---

Create a jail using a pkg-list to install requirements

	wget https://raw.githubusercontent.com/reloxx13/TasmoAdmin/master/.iocage/tasmoadmin-pkgs.json
	sudo iocage create -r 11.2-RELEASE boot=on dhcp=on bpf=yes vnet=on -p tasmoadmin-pkgs.json -n tasmoadmin


Download TasmoAdmin and get it running with nginx

	sudo iocage exec tasmoadmin git clone https://github.com/reloxx13/TasmoAdmin.git /root/TasmoAdmin
	sudo iocage exec tasmoadmin bash /root/TasmoAdmin/.iocage/tasmoadmin-install.sh

You should now be able to use TasmoAdmin by entering `http://YOUR.TASMOADMIN.IP.ADDRESS` in your browser



---

To see a list of jails as well as their ip address

    sudo iocage list -l
    
    +-----+------------+------+-------+------+-----------------+---------------------+-----+----------+
    | JID |    NAME    | BOOT | STATE | TYPE |     RELEASE     |         IP4         | IP6 | TEMPLATE |
    +=====+============+======+=======+======+=================+=====================+=====+==========+
    | 1   | tasmoadmin | on   | up    | jail | 11.2-RELEASE-p3 | epair0b|192.0.1.126 | -   | -        |
    +-----+------------+------+-------+------+-----------------+---------------------+-----+----------+
 
