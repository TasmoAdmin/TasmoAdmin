# iocage-tasmoadmin
Artifact file(s) for [TasmoAdmin](https://github.com/reloxx13/TasmoAdmin)

---
## iocage-plugin-tasmoadmin

 - This script will by default create a plugin-jail for TasmoAdmin using *nginx and php72* on FreeNAS 11.2 

**Download plugin and install**

    wget -O /tmp/tasmoadmin.json https://raw.githubusercontent.com/reloxx13/TasmoAdmin/master/.iocage/tasmoadmin.json
    sudo iocage fetch -P dhcp=on vnet=on bpf=yes -n /tmp/tasmoadmin.json --branch 'master'

---
---
### iocage-jail-tasmoadmin
 
 - This scrpit can also be used to create a standard-jail for the same *TasmoAdmin using nginx and php72*

##### Create a jail using a pkg-list to install requirements

    wget -O /tmp/pkglist.json https://raw.githubusercontent.com/reloxx13/TasmoAdmin/master/.iocage/pkg-list.json
    sudo iocage create -r 11.2-RELEASE boot=on dhcp=on bpf=yes vnet=on -p /tmp/pkglist.json -n tasmoadmin


##### Git TasmoAdmin and install

    sudo iocage exec tasmoadmin git https://github.com/reloxx13/TasmoAdmin.git /root/TasmoAdmin
    sudo iocage exec tasmoadmin bash /root/TasmoAdmin/post_install.sh standard

---

 - You should now be able to use TasmoAdmin by entering `http://YOUR.TASMOADMIN.IP.ADDRESS` in your browser
 
###### Reset the TasmoAdmin login

    sudo iocage exec tasmoadmin tasmo-pwreset

###### To see a list of jails as well as their ip address

    sudo iocage list -l
    +-----+--------------+------+-------+----------+-----------------+---------------------+-----+----------+
    | JID |     NAME     | BOOT | STATE |   TYPE   |     RELEASE     |         IP4         | IP6 | TEMPLATE |
    +=====+==============+======+=======+==========+=================+=====================+=====+==========+
    | 1   | tasmoadmin   | on   | up    | jail     | 11.2-RELEASE-p4 | epair0b|192.0.1.73  | -   | -        |
    +-----+--------------+------+-------+----------+-----------------+---------------------+-----+----------+
    | 2   | tasmoadmin_2 | on   | up    | pluginv2 | 11.2-RELEASE-p4 | epair0b|192.0.1.76  | -   | -        |
    +-----+--------------+------+-------+----------+-----------------+---------------------+-----+----------+


Tested on FreeNAS-11.2-BETA3  
More information about [iocage plugins](https://doc.freenas.org/11.2/plugins.html) and [iocage jails](https://doc.freenas.org/11.2/jails.html) can be found in the [FreeNAS guide](https://doc.freenas.org/11.2/intro.html#introduction)  
This script should also still work with FreeNAS 11.1

