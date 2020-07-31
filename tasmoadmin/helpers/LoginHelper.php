<?php

class LoginHelper
{
    public static function login($password, $storedPassword)
    {

        $Config = new Config();

        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

        //update hashing
        if (strpos($storedPassword, '$2y$') !== 0) {
            if ($storedPassword !== md5($password)) {
                return false;
            }

            $upgradedPassword = self::hashPassword($password);

            $Config->write("password", $upgradedPassword);
 
            return true;
        }

        if (!password_verify($password, $storedPassword)) {
            return false;
        }

        return true;
    }

    public static function register($username, $password)
    {
        $Config = new Config();

        $Config->write("username", $username);
        $Config->write("password", self::hashPassword($password));


        return true;
    }

    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
