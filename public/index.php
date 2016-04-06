<?php
require_once('config/Initialize.php');

/*
 * I lied, this is no autoload. NOT YET! Don't push me :'(
 * 
 * Core modules. I call them classes. Because I can!
 * These I consider Core. There will be no EoL php-Project without these.
 * You may not use routing in any of the core classes, there should be zero
 * user interaction here. Just don't touch them is all I am saying, mmmkay?
 */
require_once ('classes/autoload.php');
/*
 * Optional modules
 * 
 * Most websites will be useless without these and most Modules will need
 *  them as well.
 * Classes are instantiated inside the {Classname}.php file and a call to
 *  the initEnv() function is made, if available. I marked those classes
 *  with (*) in this file.
 * I chose the initEnv approach to have on-Load functionality in classes in
 *  addition to the on-Instantiate ones. Feel free to optimize :)
 */
require_once ('modules/autoload.php');

/*
 * Set up the static::template to use
 */
$page = Page::getInstance();
$page->setTmpl(file('views/core/page.php'));

/*
 * now comes a lot of quickly hacked together stuff. I needed some results way
 * before I could lay a proper foundation. This IS a work in progress and
 * there's lots of stuff to do. Wanna help? Feel free ^^
 */
$page->addContent('{##header##}', '<h1>Evolution of Loneliness</h1>');

$site_menu  = '<div class="site-menu">';
$site_menu .= '<a href="/">Home</a>';
$site_menu .= '<a href="/activities">Activities</a>';
$site_menu .= '<a href="/profiles">Members</a>';
$site_menu .= '<a href="/about">About EoL</a>';
$site_menu .= '</div>';

$user_menu  = '<div class="user-menu">';
$user_menu .= ($login->isLoggedIn()) ? '<a href="/profile/' . $login->currentUsername() . '">Profile</a> ' : '<a href="/register">Register </a>';
$user_menu .= ($login->isLoggedIn()) ? '<a href="/logout">Logout</a>' : '<a href="/login">Login</a>';
$user_menu .= '</div>';
$page->addContent('{##nav##}', $site_menu . $user_menu);

// $page->addContent('{##sidebar##}', 'widgets');

/*
 * Do the routing as per modules instructions!!
 * Can be overridden here
 */
Toro::addRoute(["/" => "Home"]);
ToroHook::add('404', function() {
    header('HTTP/1.0 404 Not Found');
    $page = Page::getInstance();
    $page->setTmpl(file('views/core/404.php'));
    $page->replaceTags();
    echo $page;
    exit;
});
Toro::serve();
/*
 * You did it! This website is being displayed now ;)
 */
$page->replaceTags();
echo $page;
