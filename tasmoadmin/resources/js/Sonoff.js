/**
 * Your classic Sonoff
 * @typedef {Object} Sonoff
 * @method getStatus
 * @property {int} timeout Current state of the Sonoff
 */

class Sonoff {
    constructor(options) {
        this.options = {
            timeout: 10
        };

        $.extend(this.options, options);
    }

    /**
     * getStatus
     *
     * @param {string} ip
     * @param {int} id
     * @param {callback} callback
     */
    getStatus(ip, id, callback) {
        var cmnd = "Status 0";

        this._doAjax(ip, id, cmnd, callback);
    }

    /**
     * getAllStatus
     *
     * @param {int} timeout
     * @param {callback} callback
     */
    getAllStatus(timeout, callback) {
        var cmnd = "Status 0";

        this._doAjaxAll(timeout, cmnd, callback);
    }

    updateConfig(device_id, cmnd, newvalue, callback) {
        var cmnd = cmnd + " " + newvalue;

        this._doAjax(null, device_id, cmnd, callback);
    }

    generic(device_id, cmnd, newvalue, callback) {
        var newvalue = (
            (
                newvalue !== undefined
            ) ? " " + newvalue : ""
        );
        var cmnd = cmnd + newvalue;

        this._doAjax(null, device_id, cmnd, callback);
    }

    /**
     * getStatus
     *
     * @param {string} ip
     * @param {int} id
     * @param {int} relais
     * @param {function} callback
     */
    toggle(ip, id, relais, callback) {
        relais = relais || 1;
        var cmnd = "Power" + relais + " toggle";

        console.log("[Sonoff][toggle][" + ip + "][Relais" + relais + "] cmnd => " + cmnd);

        this._doAjax(ip, id, cmnd, callback);
    }

    /**
     * getStatus
     *
     * @param {string} ip
     * @param {int} id
     * @param {int} relais
     * @param {function} callback
     */
    off(ip, id, relais, callback) {
        relais = relais || 1;
        var cmnd = "Power" + relais + " 0";

        console.log("[Sonoff][toggle][" + ip + "][Relais" + relais + "] cmnd => " + cmnd);

        this._doAjax(ip, id, cmnd, callback);

    }

    /**
     * _doAjax
     * @param {string} ip
     * @param {int} id
     * @param {string} cmnd
     * @param {callback} callback
     * @private
     */
    _doAjax(ip, id, cmnd, callback) {
        var ip = ip || id;
        $.ajax({
            dataType: "json",
            url: "?doAjax",
            timeout: this.options.timeout * 1000,
            cache: false,
            type: "post",
            async: true,
            data: {
                id: id,
                cmnd: encodeURIComponent(cmnd)
            },
            success: function (data) {
                // var data = data || { ERROR : "NO DATA" };

                //console.log( "[Sonoff][doAjax][" + ip + "] Response from: " + cmnd + " => " + JSON.stringify(
                //   data ) );
                console.log("[Sonoff][doAjax][" + ip + "] Got response from: " + cmnd);

                if (data.WARNING) {
                    alert(ip + ": " + data.WARNING);
                }
                if (callback !== undefined) {
                    callback(data);
                }
            },
            error: function (data, xmlhttprequest, textstatus, message) {
                if (callback !== undefined) {
                    callback(data);
                }
            }
        });
    }

    /**
     * _doAjaxAll
     * @param {int} timeout
     * @param {string} cmnd
     * @param {callback} callback
     * @private
     */
    _doAjaxAll(timeout, cmnd, callback) {
        var timeout = timeout || this.options.timeout;
        $.ajax({
            dataType: "json",
            url: "?doAjaxAll",
            timeout: timeout * 1000,
            cache: false,
            type: "post",
            data: {
                cmnd: encodeURIComponent(cmnd)
            },
            success: function (data) {
                // var data = data || { ERROR : "NO DATA" };

                //console.log( "[Sonoff][doAjax][" + ip + "] Response from: " + cmnd + " => " + JSON.stringify(
                //   data ) );
                console.log("[Sonoff][doAjaxAll] Got response from: " + cmnd);


                if (data.WARNING) {
                    alert(ip + ": " + data.WARNING);
                }
                if (callback !== undefined) {
                    callback(data);
                }
            },
            error: function (data, xmlhttprequest, textstatus, message) {
                if (callback !== undefined) {
                    callback(data);
                }
            }
        });
    }

    /**
     * parseDeviceStatus
     * @param {object} data
     * @param {int} device_relais
     * @returns {string}
     */
    parseDeviceStatus(data, device_relais) {
        let device_status = "NONE";

        if (data.StatusSTS !== undefined) {
            if (device_relais !== undefined && eval("data.StatusSTS.POWER" + device_relais) !== undefined) {
                if (eval("data.StatusSTS.POWER" + device_relais + ".STATE") !== undefined) {
                    device_status = eval("data.StatusSTS.POWER" + device_relais + ".STATE");
                } else {
                    device_status = eval("data.StatusSTS.POWER" + device_relais);
                }
            } else {
                if (data.StatusSTS.POWER !== undefined) {
                    if (data.StatusSTS.POWER.STATE !== undefined) {
                        device_status = data.StatusSTS.POWER.STATE;
                    } else {
                        device_status = data.StatusSTS.POWER;
                    }
                }
            }
        } else {
            if (device_relais !== undefined && eval("data.POWER" + device_relais) !== undefined) {

                if (eval("data.POWER" + device_relais + ".STATE") !== undefined) {
                    device_status = eval("data.POWER" + device_relais + ".STATE");
                } else {
                    device_status = eval("data.POWER" + device_relais);
                }
            } else {
                if (data.POWER !== undefined) {
                    if (data.POWER.STATE !== undefined) {
                        device_status = data.POWER.STATE;
                    } else {
                        device_status = data.POWER;
                    }
                }
            }
        }

        return device_status;
    }

    /**
     * parseDeviceHostname
     * @param {object} data
     * @returns {boolean|string}
     */
    parseDeviceHostname(data) {
        var device_hostname = false;

        if (data.StatusNET !== undefined) {
            if (data.StatusNET.Hostname !== undefined) {
                device_hostname = data.StatusNET.Hostname;
            }

        }
        return device_hostname;
    }

    /**
     * directAjax
     *
     * @param {string} url
     */
    directAjax(url) {
        $.ajax({
            url: url,
            timeout: this.options.timeout * 1000,
            cache: false,
            success: function (data) {

            },
            error: function (data, xmlhttprequest, textstatus, message) {

            }
        });
    }

    setDeviceValue(id, field, newvalue, td) {
        $.ajax({
            dataType: "json",
            url: "?doAjax",
            timeout: this.options.timeout * 1000,
            cache: false,
            type: "post",
            data: {
                id: id,
                field: encodeURIComponent(field),
                newvalue: encodeURIComponent(newvalue),
                target: "csv"
            },
            success: function (data) {
                // var data = data || { ERROR : "NO DATA" };

                console.log("[Sonoff][doAjax][" + id + "] Response from: " + field + " => " + JSON.stringify(
                    data));
                console.log("[Sonoff][doAjax][" + id + "] Got response from: " + field + " => " + newvalue);

                td.html(data.position);


            },
            error: function (data, xmlhttprequest, textstatus, message) {
                console.log("ERROR setDeviceValue");
            }
        });
    }

    parseEnergyPower(data, joinString = "<br/>")
    {
        let energyPower = [];
        if (data.StatusSNS.ENERGY !== undefined) {
            if (data.StatusSNS.ENERGY.Power !== undefined) {
                energyPower.push(data.StatusSNS.ENERGY.Power + " W");
            }

            if (data.StatusSNS.ENERGY.Today !== undefined) {
                let tmpString = data.StatusSNS.ENERGY.Today;
                if (data.StatusSNS.ENERGY.Yesterday !== undefined) {
                    tmpString += " / " + data.StatusSNS.ENERGY.Yesterday;
                }

                if (data.StatusSNS.ENERGY.Total !== undefined) {
                    tmpString += " / " + data.StatusSNS.ENERGY.Total;
                }

                energyPower.push(tmpString + " kWh");
            }

            if (data.StatusSNS.ENERGY.Current !== undefined) {
                energyPower.push(data.StatusSNS.ENERGY.Current + " A");
            }
        }

        return energyPower.join(joinString);
    }

    parseStatusData(data) {
        data.hasWifi = false;

        if (Object.hasOwn(data, 'StatusSTS')) {
            data.hasWifi = true;

            let version = this._parseVersion(data.StatusFWR.Version);
            let wifi;
            if (version >= 510009) {//no json translations since 5.10.0j
                wifi = {
                    rssi: data.StatusSTS.Wifi.RSSI,
                    ssid: data.StatusSTS.Wifi.SSId,
                    uptime: data.StatusSTS.Wifi.Uptime,
                };
            } else { //try german else use english
                wifi = {
                    rssi: data.StatusSTS.WLAN ? data.StatusSTS.WLAN.RSSI : data.StatusSTS.Wifi.RSSI,
                    ssid: data.StatusSTS.WLAN ? data.StatusSTS.WLAN.SSID : data.StatusSTS.Wifi.SSId,
                    uptime: data.StatusSTS.Laufzeit ? data.StatusSTS.Laufzeit : data.StatusSTS.Uptime,
                };
            }

            data.wifi = wifi;
        }

        return data;
    }

    _parseVersion (versionString)
    {
        versionString = versionString.replace("-minimal", "").replace(/\./g, "");

        var last = versionString.slice(-1);
        if (isNaN(last)) {
            versionString = versionString.replace(
                last,
                (
                    last.charCodeAt(0) - 97 < 10
                        ? "0" + (
                        last.charCodeAt(0) - 97
                    )
                        : last.charCodeAt(0) - 97
                )
            );
        } else {
            versionString = versionString + "00";
        }

        return versionString;
    }
}
