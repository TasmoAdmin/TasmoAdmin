<?php

namespace TasmoAdmin;

class Device
{
    public const DEFAULT_IMAGE = 'bulb_1';

    public ?int $id;
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
        ?int $id,
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
    ) {
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

    public function getName(): string
    {
        return implode('-', $this->names);
    }

    public function getUrlWithAuth(): string
    {
        $auth = '';
        if (!empty($this->username) && !empty($this->password)) {
            $auth = sprintf('%s:%s@', $this->username, $this->password);
        }

        return sprintf('http://%s%s', $auth, $this->ip);
    }
}
