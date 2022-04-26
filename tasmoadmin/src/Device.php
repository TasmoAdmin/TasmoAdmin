<?php

namespace TasmoAdmin;

use stdClass;

class Device
{
    public static function fromLine(array $line): ?stdClass
    {
        if (empty($line)) {
            return null;
        }

        $device                     = new stdClass();
        $line[1]              = explode("|", $line[1]);
        $device->id                 = isset($line[0]) ? $line[0] : false;
        $device->names              = isset($line[1]) ? $line[1] : false;
        $device->ip                 = isset($line[2]) ? $line[2] : false;
        $device->username           = isset($line[3]) ? $line[3] : false;
        $device->password           = isset($line[4]) ? $line[4] : false;
        $device->img                = isset($line[5]) ? $line[5] : "bulb_1";
        $device->position           = isset($line[6]) && $line[6] != "" ? $line[6] : "";
        $device->device_all_off     = isset($line[7]) ? $line[7] : 1;
        $device->device_protect_on  = isset($line[8]) ? $line[8] : 0;
        $device->device_protect_off = isset($line[9]) ? $line[9] : 0;

        $keywords   = [];
        $keywords[] = count($device->names) > 1 ? "multi" : "single";
        $keywords[] = "IP#" . $device->ip;
        $keywords[] = "ID#" . $device->id;
        $keywords[] = "POS#" . $device->position;

        $device->keywords = $keywords;
        return $device;
    }
}
