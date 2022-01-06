<?php

class LoginHelper
{
    
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function login($password, $storedPassword)
    {
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

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

    public function register($username, $password)
    {
        $this->config->write("username", $username);
        $this->config->write("password", self::hashPassword($password));
    }

    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
