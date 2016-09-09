<?php 
require_once('toro/Toro.php'); // url requests
require_once('parsedown/Parsedown.php'); //
// require_once('markdown/Markdown.php'); // Markdown Library
require_once('view/View.php'); // template based output-containers
// static container for the page-template
require_once('view/Page.php'); // Page::getInstance();
// your get and post data, sanitized!
require_once('env/Env.php'); // Env::getInstance();
require_once('db/db.php'); // db::getInstance();
require_once('msg/Msg.php'); // feedback for user input & actions


require_once('mail/class.phpmailer.php');
require_once('mail/class.smtp.php'); //
