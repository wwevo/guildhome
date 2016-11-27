<?php
header("Content-Type: text/html;charset=utf-8");
include_once 'config/env.php';
/* 
 * config/env.php:
 * <?php
 * define('GH_BASEDIR', '');
 *
 */

if (constant('GH_BASEDIR') == 'beta.eol.gw2.localhost') {
    error_reporting(E_ALL | E_STRICT);
    //error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", 1);
} else {
    error_reporting(E_ERROR);
    ini_set("display_errors", 0);
}

define('default_theme', 'boilerplate');

session_start(); // we need sessions in this one, make sure it's started
$now = time();
if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
    session_unset(); // this session has worn out its welcome; 
    session_destroy(); // kill it...
    session_start(); // ...and start a brand new one
}
$_SESSION['discard_after'] = $now + 3600;

// checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("The PHP Version used is way to old.");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require_once("libraries/password_compatibility_library.php");
}
