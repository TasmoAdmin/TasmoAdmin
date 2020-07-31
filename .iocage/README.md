### TasmoAdmin plugin for FreeNAS 11 using *nginx* and *php72*

#### FreeNAS 11.3

**Download plugin and install**

```bash
wget -O /tmp/tasmoadmin.json https://raw.githubusercontent.com/tprelog/iocage-tasmoadmin/11.3-RELEASE/tasmoadmin.json
sudo iocage fetch -P /tmp/tasmoadmin.json
```

#### FreeNAS 11.2

**Download plugin and install**

```bash
wget -O /tmp/tasmoadmin.json https://raw.githubusercontent.com/tprelog/iocage-tasmoadmin/11.2-RELEASE/tasmoadmin.json
sudo iocage fetch -P dhcp=on vnet=on bpf=yes -n /tmp/tasmoadmin.json
```

---

##### Reset the TasmoAdmin login

```bash
sudo iocage exec tasmoadmin tasmo-pwreset
```
