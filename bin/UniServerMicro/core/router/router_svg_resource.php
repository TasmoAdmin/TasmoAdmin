<?php
//Serve requests for pages containing svg images
$path = pathinfo($_SERVER['SCRIPT_FILENAME']); //Get request
if($path['extension'] == 'svg') {              //Check file extension
    header('Content-Type: image/svg+xml');     //Create header 
    readfile($_SERVER['SCRIPT_FILENAME']);     //Read and server this page
    exit;
}
else return FALSE;
?>