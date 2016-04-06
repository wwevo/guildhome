<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

header("Content-Type: text/html;charset=utf-8");
date_default_timezone_set('UTC');
 error_reporting(E_ALL);
//error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

// we need sessions in this one, make sure it's started
if (!session_id()) {
    session_start();
}

// checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require_once("libraries/password_compatibility_library.php");
}
