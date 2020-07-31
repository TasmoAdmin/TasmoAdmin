<?php

class Login
{
    public static function login($password, $storedPassword)
    {
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

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
        $Config->write("username", $username);
        $Config->write("password", self::hashPassword($password));
    }

    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
