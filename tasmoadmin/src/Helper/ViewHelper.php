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
        $configNightMode = $this->config->read("nightmode");
        $nightMode = "";

        if ($configNightMode === "disable") {
            return $nightMode;
        }

        if ($configNightMode === "auto") {
            if ($hour >= 18 || $hour <= 8) {
                $nightMode = "nightmode";
            }
        }
        elseif ($configNightMode === "always") {
            $nightMode = "nightmode";
        }

        return $nightMode;
    }


}
