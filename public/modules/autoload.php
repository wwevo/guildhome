<?php 
require_once('login/Login.php'); // (*) requires db
require_once('profile/Profile.php'); // (*) requires db
require_once('login/Register.php'); // (*) requires db
require_once('settings/Settings.php'); // (*)
/*
 * Site specific Modules.
 */
require_once('identity/Identity.php'); // (*)
require_once('activity/Activity.php'); // (*)
require_once('activity/Comment.php'); // (*)
require_once('activity/Activity_Shout.php'); // (*)
require_once('activity/Activity_Event.php'); // (*)
require_once('home/Home.php'); // (*)
require_once('about/About.php'); // (*)
require_once('gw2api/gw2api.php'); // (*)
