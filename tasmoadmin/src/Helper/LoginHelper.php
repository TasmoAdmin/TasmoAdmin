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
        // update hashing
        if (!str_starts_with($storedPassword, '$2y$')) {
            if ($storedPassword !== md5($password)) {
                return false;
            }

            $upgradedPassword = self::hashPassword($password);

            $this->config->write('password', $upgradedPassword);

            return true;
        }

        return password_verify($password, $storedPassword);
    }

    public function register(string $username, string $password): void
    {
        $this->config->write('username', $username);
        $this->config->write('password', self::hashPassword($password));
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
