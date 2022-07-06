<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class OtaHelper
{
    private const FIRMWARE_PATH = 'data/firmwares/';

    private Config $config;
    private string $baseUrl;

    public function __construct(Config $config, string $baseUrl)
    {
        $this->config = $config;
        $this->baseUrl = $baseUrl;
    }

    public function getFirmwareUrl(string $firmware): string
    {
        return $this->getOtaServer() . basename($firmware);
    }

    private function getOtaServer(): string
    {
        return sprintf('%s://%s:%s%s%s',
            $this->config->schema(),
            $this->config->read('ota_server_ip'),
            $this->config->read('ota_server_port'),
            $this->baseUrl,
            self::FIRMWARE_PATH,
        );
    }
}
