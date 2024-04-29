<?php

namespace TasmoAdmin;

class DeviceFactory
{
    public static function fakeDevice(string $ip, int $port, string $username, string $password): Device
    {
        return new Device(
            null,
            [],
            $ip,
            $username,
            $password,
            port: $port
        );
    }

    public static function fromRequest(array $request): Device
    {
        $device = [];
        $device[0] = $request['device_id'];
        $device[1] = implode('|', $request['device_name']);
        $device[2] = $request['device_ip'];
        $device[3] = $request['device_username'];
        $device[4] = $request['device_password'];
        $device[5] = $request['device_img'] ?? Device::DEFAULT_IMAGE;
        $device[6] = $request['device_position'] ?? 0;
        $device[7] = $request['device_all_off'] ?? true;
        $device[8] = $request['device_protect_on'] ?? false;
        $device[9] = $request['device_protect_off'] ?? false;
        $device[10] = $request['is_updatable'] ?? true;
        $device[11] = $request['device_port'] ?? Device::DEFAULT_PORT;

        return self::fromArray($device);
    }

    public static function fromArray(array $array): ?Device
    {
        if (empty($array)) {
            return null;
        }

        $array[1] = explode('|', $array[1] ?? '');

        $id = $array[0] ?? null;
        $names = $array[1];
        $ip = $array[2] ?? false;
        $username = $array[3] ?? false;
        $password = $array[4] ?? false;
        $img = $array[5] ?? Device::DEFAULT_IMAGE;
        $position = $array[6] ?? 0;
        $device_all_off = $array[7] ?? true;
        $device_protect_on = $array[8] ?? false;
        $device_protect_off = $array[9] ?? false;
        $is_updatable = $array[10] ?? true;
        $port = $array[11] ?? Device::DEFAULT_PORT;

        $keywords = [];
        $keywords[] = count($names) > 1 ? 'multi' : 'single';
        $keywords[] = 'IP#'.$ip;
        $keywords[] = 'ID#'.$id;
        $keywords[] = 'POS#'.$position;

        return new Device(
            $id,
            $names,
            $ip,
            $username,
            $password,
            $img,
            (int) $position,
            (bool) $device_all_off,
            (bool) $device_protect_on,
            (bool) $device_protect_off,
            $keywords,
            (bool) $is_updatable,
            (int) $port,
        );
    }
}
