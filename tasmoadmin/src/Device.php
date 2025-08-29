<?php

namespace TasmoAdmin;

class Device
{
    public const DEFAULT_IMAGE = 'bulb_1';
    public const DEFAULT_PORT = 80;

    public ?int $id;
    public array $names;
    public string $ip;
    public int $port;
    public string $username;
    public string $password;
    public string $img;
    public int $position;
    public bool $deviceAllOff;
    public bool $deviceProtectionOn;
    public bool $deviceProtectionOff;
    public array $keywords;
    public bool $isUpdatable;

    public function __construct(
        ?int $id,
        array $names,
        string $ip,
        string $username,
        string $password,
        string $img = self::DEFAULT_IMAGE,
        int $position = 1,
        bool $deviceAllOff = true,
        bool $deviceProtectionOn = false,
        bool $deviceProtectionOff = false,
        array $keywords = [],
        bool $isUpdatable = true,
        int $port = self::DEFAULT_PORT
    ) {
        $this->id = $id;
        $this->names = $names;
        $this->ip = $ip;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->img = $img;
        $this->position = $position;
        $this->deviceAllOff = $deviceAllOff;
        $this->deviceProtectionOn = $deviceProtectionOn;
        $this->deviceProtectionOff = $deviceProtectionOff;
        $this->keywords = $keywords;
        $this->isUpdatable = $isUpdatable;
    }

    public function getAddress(): string
    {
        return $this->ip.':'.$this->port;
    }

    public function getName(): string
    {
        return implode('-', $this->names);
    }

    public function getBackupName(): string
    {
        $pathSafeName = $this->getName();
        $pathSafeName = str_replace('/', '_', $pathSafeName);

        return sprintf('%s-%s.dmp', $this->id, $pathSafeName);
    }

    public function getUrlWithAuth(): string
    {
        $auth = '';
        if (!empty($this->username) && !empty($this->password)) {
            $auth = sprintf('%s:%s@', $this->username, $this->password);
        }

        return sprintf('http://%s%s', $auth, $this->getAddress());
    }
}
