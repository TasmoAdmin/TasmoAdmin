<?php


$_SESSION[ 'lang' ] = $new_lang;
$redirect = $_GET['current'] ?? _BASEURL_;
header("Location: $redirect");
