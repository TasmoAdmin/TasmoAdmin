<?php

namespace TasmoAdmin;

class Device
{
    public const DEFAULT_IMAGE = 'bulb_1';

    public int $id;
    public array $names;
    public string $ip;
    public string $username;
    public string $password;
    public string $img;
    public string $position;
    public int $deviceAllOff;
    public int $deviceProtectionOn;
    public int $deviceProtectionOff;
    public array $keywords;

    public function __construct(
        int $id,
        array $names,
        string $ip,
        string $username,
        string $password,
        string $img,
        string $position,
        int $deviceAllOff,
        int $deviceProtectionOn,
        int $deviceProtectionOff,
        array $keywords
    )
    {
        $this->id = $id;
        $this->names = $names;
        $this->ip = $ip;
        $this->username = $username;
        $this->password = $password;
        $this->img = $img;
        $this->position = $position;
        $this->deviceAllOff = $deviceAllOff;
        $this->deviceProtectionOn = $deviceProtectionOn;
        $this->deviceProtectionOff = $deviceProtectionOff;
        $this->keywords = $keywords;
    }


    public static function fromLine(array $line): ?Device
    {
        if (empty($line)) {
            return null;
        }

        $line[1] = explode("|", $line[1]);
        
        $id = isset($line[0]) ? $line[0] : false;
        $names = isset($line[1]) ? $line[1] : false;
        $ip = isset($line[2]) ? $line[2] : false;
        $username = isset($line[3]) ? $line[3] : false;
        $password = isset($line[4]) ? $line[4] : false;
        $img = isset($line[5]) ? $line[5] : self::DEFAULT_IMAGE;
        $position = isset($line[6]) && $line[6] != "" ? $line[6] : "";
        $device_all_off = isset($line[7]) ? $line[7] : 1;
        $device_protect_on = isset($line[8]) ? $line[8] : 0;
        $device_protect_off = isset($line[9]) ? $line[9] : 0;

        $keywords = [];
        $keywords[] = count($names) > 1 ? "multi" : "single";
        $keywords[] = "IP#" . $ip;
        $keywords[] = "ID#" . $id;
        $keywords[] = "POS#" . $position;


        return new self(
            $id,
            $names,
            $ip,
            $username,
            $password,
            $img,
            $position,
            $device_all_off,
            $device_protect_on,
            $device_protect_off,
            $keywords
        );
    }
}
