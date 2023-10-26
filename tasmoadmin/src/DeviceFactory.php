<?php

namespace TasmoAdmin;

class DeviceFactory
{
    public static function fakeDevice(string $ip, string $username, string $password): Device
    {
        return new Device(
            null,
            [],
            $ip,
            $username,
            $password,
            Device::DEFAULT_IMAGE,
            1,
            1,
            0,
            0,
            [],
            true
        );
    }

    public static function fromRequest(array $request): Device
    {
        $device  = [];
        $device[0] = $request["device_id"];
        $device[1] = implode("|", $request["device_name"]);
        $device[2] = $request["device_ip"];
        $device[3] = $request["device_username"];
        $device[4] = $request["device_password"];
        $device[5] = $request["device_img"] ?? Device::DEFAULT_IMAGE;
        $device[6] = $request["device_position"] ?? 0;
        $device[7] = $request["device_all_off"] ?? 1;
        $device[8] = $request["device_protect_on"] ?? 0;
        $device[9] = $request["device_protect_off"] ?? 0;
        $device[10] = $request["is_updatable"] ?? true;

        return self::fromArray($device);
    }

    public static function fromArray(array $array): ?Device
    {
        if (empty($array)) {
            return null;
        }

        $array[1] = explode("|", $array[1] ?? '');

        $id = $array[0] ?? null;
        $names = $array[1];
        $ip = $array[2] ?? false;
        $username = $array[3] ?? false;
        $password = $array[4] ?? false;
        $img = $array[5] ?? Device::DEFAULT_IMAGE;
        $position = $array[6] ?? 0;
        $device_all_off = $array[7] ?? 1;
        $device_protect_on = $array[8] ?? 0;
        $device_protect_off = $array[9] ?? 0;
        $is_updatable = $array[10] ?? true;

        $keywords = [];
        $keywords[] = count($names) > 1 ? "multi" : "single";
        $keywords[] = "IP#" . $ip;
        $keywords[] = "ID#" . $id;
        $keywords[] = "POS#" . $position;


        return new Device(
            $id,
            $names,
            $ip,
            $username,
            $password,
            $img,
            (int) $position,
            $device_all_off,
            $device_protect_on,
            $device_protect_off,
            $keywords,
            $is_updatable
        );
    }
}
