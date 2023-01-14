<?php


ob_start();

session_unset();
session_destroy();
if (isset($_COOKIE['MyConfig'])) {
    unset($_COOKIE['MyConfig']);
    setcookie('MyConfig', '', time() - 3600, '/'); // empty value and old timestamp
}

header("Location: " . _BASEURL_ . "login");
ob_end_flush();
