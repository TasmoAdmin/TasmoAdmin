<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class ViewHelper
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function getNightMode(int $hour): string
    {
        $configNightMode = $this->config->read('nightmode');
        $nightMode = '';

        if ('disable' === $configNightMode) {
            return $nightMode;
        }

        if ('auto' === $configNightMode) {
            if ($hour >= 18 || $hour <= 8) {
                $nightMode = 'nightmode';
            }
        } elseif ('always' === $configNightMode) {
            $nightMode = 'nightmode';
        }

        return $nightMode;
    }
}
