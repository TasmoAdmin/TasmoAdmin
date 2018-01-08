<?php

//MPG 10-5-2012
//Serve requests for old htm pages. Requires content header

$path = pathinfo($_SERVER['SCRIPT_FILENAME']); //Get request
if($path['extension'] == 'htm') {              //Check file extension
    header('Content-Type: text/html');         //Create header 
    readfile($_SERVER['SCRIPT_FILENAME']);     //Read and server this page
    exit;
}
else return FALSE;
?>