<?php

namespace TasmoAdmin\Helper;

use TasmoAdmin\Config;

class LoginHelper
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function login(string $password, string $storedPassword): bool
    {
        //update hashing
        if (strpos($storedPassword, '$2y$') !== 0) {
            if ($storedPassword !== md5($password)) {
                return false;
            }

            $upgradedPassword = self::hashPassword($password);

            $this->config->write("password", $upgradedPassword);

            return true;
        }

        if (!password_verify($password, $storedPassword)) {
            return false;
        }

        return true;
    }

    public function register(string $username, string $password): void
    {
        $this->config->write("username", $username);
        $this->config->write("password", self::hashPassword($password));
    }

    private static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
