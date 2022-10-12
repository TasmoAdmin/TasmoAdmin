export function parseVersion(versionString)
{
    versionString = versionString.replace("-minimal", "").replace(/\./g, "");

    var last = versionString.slice(-1);
    if (isNaN(last))
    {
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
    } else
    {
        versionString = versionString + "00";
    }

    return versionString;
}

export function getEnergyPower(data, joinString)
{
    var enerygPower = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.ENERGY !== undefined)
    {
        if (data.StatusSNS.ENERGY.Power !== undefined)
        {
            enerygPower.push(data.StatusSNS.ENERGY.Power + "W");
        }

        if (data.StatusSNS.ENERGY.Today !== undefined)
        {
            var tmpString = data.StatusSNS.ENERGY.Today;
            if (data.StatusSNS.ENERGY.Yesterday !== undefined)
            {
                tmpString += "/" + data.StatusSNS.ENERGY.Yesterday;
            }
            enerygPower.push(tmpString + "kWh");
        }

        if (data.StatusSNS.ENERGY.Current !== undefined)
        {
            enerygPower.push(data.StatusSNS.ENERGY.Current + "A");
        }
    }

    return enerygPower.join(joinString);
}

export function getTemp(data, joinString)
{
    var temp = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.TempUnit === undefined)
    {
        data.StatusSNS.TempUnit = "F";
    }

    if (data.StatusSNS.DS18B20 !== undefined)
    {
        temp.push((
            data.StatusSNS.DS18B20.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.DS18x20 !== undefined)
    {
        if (data.StatusSNS.DS18x20.DS1 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS1.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS2 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS2.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS3 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS3.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS4 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS4.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS5 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS5.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS6 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS6.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS7 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS7.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
        if (data.StatusSNS.DS18x20.DS8 !== undefined)
        {
            temp.push((
                data.StatusSNS.DS18x20.DS8.Temperature + "°" + data.StatusSNS.TempUnit
            ));
        }
    }

    //6.1.1c 20180904
    if (data.StatusSNS["DS18B20-1"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-1"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-2"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-2"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-3"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-3"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-4"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-4"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-5"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-5"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-6"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-6"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-7"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-7"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["DS18B20-8"] !== undefined)
    {
        temp.push((
            data.StatusSNS["DS18B20-8"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }


    if (data.StatusSNS.DHT11 !== undefined)
    {
        temp.push((
            data.StatusSNS.DHT11.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.AM2301 !== undefined)
    {
        temp.push((
            data.StatusSNS.AM2301.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.SHT3X !== undefined)
    {
        temp.push((
            data.StatusSNS.SHT3X.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["SHT3X-0x45"] !== undefined)
    {
        temp.push((
            data.StatusSNS["SHT3X-0x45"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.BMP280 !== undefined)
    {
        temp.push((
            data.StatusSNS.BMP280.Temperature + "°" + data.StatusSNS.TempUnit
        ));

    }
    if (data.StatusSNS.BME680 !== undefined)
    {
        temp.push((
            data.StatusSNS.BME680.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.BME280 !== undefined)
    {
        temp.push((
            data.StatusSNS.BME280.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["BME280-76"] !== undefined)
    {
        temp.push(data.StatusSNS["BME280-76"].Temperature + "°" + data.StatusSNS.TempUnit);
    }
    if (data.StatusSNS["BME280-77"] !== undefined)
    {
        temp.push(data.StatusSNS["BME280-77"].Temperature + "°" + data.StatusSNS.TempUnit);
    }
    if (data.StatusSNS.SI7021 !== undefined)
    {
        temp.push((
            data.StatusSNS.SI7021.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.HTU21 !== undefined)
    {
        temp.push((
            data.StatusSNS.HTU21.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.BMP180 !== undefined)
    {
        temp.push((
            data.StatusSNS.BMP180.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.LM75AD !== undefined)
    {
        temp.push((
            data.StatusSNS.LM75AD.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS.MAX31855 !== undefined)
    {
        temp.push((
            data.StatusSNS.MAX31855.ProbeTemperature + "°" + data.StatusSNS.TempUnit
        ));
        //temp.push( (
        //	           data.StatusSNS.MAX31855.ReferenceTemperature + "°" + data.StatusSNS.TempUnit
        //           ) );
    }


    if (data.StatusSNS.AHT1X !== undefined)
    {
        temp.push((
            data.StatusSNS.AHT1X.Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["AHT1X-0x38"] !== undefined)
    {
        temp.push((
            data.StatusSNS["AHT1X-0x38"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }
    if (data.StatusSNS["AHT1X-0x39"] !== undefined)
    {
        temp.push((
            data.StatusSNS["AHT1X-0x39"].Temperature + "°" + data.StatusSNS.TempUnit
        ));
    }

    //console.log( temp );

    return temp.join(joinString);
}

export function getHumidity(data, joinString)
{
    var humi = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.AM2301 !== undefined)
    {
        if (data.StatusSNS.AM2301.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.AM2301.Humidity + "%");
        }
    }
    if (data.StatusSNS.BME280 !== undefined)
    {
        if (data.StatusSNS.BME280.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.BME280.Humidity + "%");
        }
    }

    if (data.StatusSNS["BME280-76"] !== undefined)
    {
        if (data.StatusSNS["BME280-76"].Humidity !== undefined)
        {
            humi.push(data.StatusSNS["BME280-76"].Humidity + "%");
        }
    }
    if (data.StatusSNS["BME280-77"] !== undefined)
    {
        if (data.StatusSNS["BME280-77"].Humidity !== undefined)
        {
            humi.push(data.StatusSNS["BME280-77"].Humidity + "%");
        }
    }
    if (data.StatusSNS.BME680 !== undefined)
    {
        if (data.StatusSNS.BME680.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.BME680.Humidity + "%");
        }
    }
    if (data.StatusSNS.DHT11 !== undefined)
    {
        if (data.StatusSNS.DHT11.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.DHT11.Humidity + "%");
        }
    }
    if (data.StatusSNS.SHT3X !== undefined)
    {
        if (data.StatusSNS.SHT3X.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.SHT3X.Humidity + "%");
        }
    }
    if (data.StatusSNS["SHT3X-0x45"] !== undefined)
    {
        if (data.StatusSNS["SHT3X-0x45"].Humidity !== undefined)
        {
            humi.push(data.StatusSNS["SHT3X-0x45"].Humidity + "%");
        }
    }
    if (data.StatusSNS.SI7021 !== undefined)
    {
        if (data.StatusSNS.SI7021.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.SI7021.Humidity + "%");
        }
    }
    if (data.StatusSNS.HTU21 !== undefined)
    {
        if (data.StatusSNS.HTU21.Humidity !== undefined)
        {
            humi.push(data.StatusSNS.HTU21.Humidity + "%");
        }
    }

    //console.log( humi );

    return humi.join(joinString);
}

export function getPressure(data, joinString)
{
    var press = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.BME280 !== undefined)
    {
        if (data.StatusSNS.BME280.Pressure !== undefined)
        {
            press.push(data.StatusSNS.BME280.Pressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS["BME280-76"] !== undefined)
    {
        if (data.StatusSNS["BME280-76"].Pressure !== undefined)
        {
            press.push(data.StatusSNS["BME280-76"].Pressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS["BME280-77"] !== undefined)
    {
        if (data.StatusSNS["BME280-77"].Pressure !== undefined)
        {
            press.push(data.StatusSNS["BME280-77"].Pressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS.BMP280 !== undefined)
    {
        if (data.StatusSNS.BMP280.Pressure !== undefined)
        {
            press.push(data.StatusSNS.BMP280.Pressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS.BME680 !== undefined)
    {
        if (data.StatusSNS.BME680.Pressure !== undefined)
        {
            press.push(data.StatusSNS.BME680.Pressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS.BMP180 !== undefined)
    {
        if (data.StatusSNS.BMP180.Pressure !== undefined)
        {
            press.push(data.StatusSNS.BMP180.Pressure + "&nbsp;hPa");
        }
    }

    //console.log( press );

    return press.join(joinString);
}


export function getSeaPressure(data, joinString)
{
    var press = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.BME280 !== undefined)
    {
        if (data.StatusSNS.BME280.SeaPressure !== undefined)
        {
            press.push(data.StatusSNS.BME280.SeaPressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS["BME280-76"] !== undefined)
    {
        if (data.StatusSNS["BME280-76"].SeaPressure !== undefined)
        {
            press.push(data.StatusSNS["BME280-76"].SeaPressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS["BME280-77"] !== undefined)
    {
        if (data.StatusSNS["BME280-77"].SeaPressure !== undefined)
        {
            press.push(data.StatusSNS["BME280-77"].SeaPressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS.BMP280 !== undefined)
    {
        if (data.StatusSNS.BMP280.SeaPressure !== undefined)
        {
            press.push(data.StatusSNS.BMP280.SeaPressure + "&nbsp;hPa");
        }
    }
    if (data.StatusSNS.BMP180 !== undefined)
    {
        if (data.StatusSNS.BMP180.SeaPressure !== undefined)
        {
            press.push(data.StatusSNS.BMP180.SeaPressure + "&nbsp;hPa");
        }
    }

    //console.log( press );

    return press.join(joinString);
}

export function getDistance(data, joinString)
{
    var dist = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.SR04 !== undefined)
    {
        if (data.StatusSNS.SR04.Distance !== undefined)
        {
            dist.push(data.StatusSNS.SR04.Distance + "cm");
        }
    }

    //console.log( press );

    return dist.join(joinString);
}

export function getGas(data, joinString)
{
    var gas = [];
    var joinString = joinString || "<br/>";

    if (data.StatusSNS.BME680 !== undefined)
    {
        if (data.StatusSNS.BME680.Gas !== undefined)
        {
            gas.push(data.StatusSNS.BME680.Gas + "kOhm");
        }
    }

    //console.log( press );

    return gas.join(joinString);
}
