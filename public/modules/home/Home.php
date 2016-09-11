<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Home
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Home {
    
    function initEnv() {
        Toro::addRoute(["/" => 'Home']);
    }
    
    function get() {
        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Home</h2>');
        $page->addContent('{##main##}', '<p>Welcome to the EoL Guild-Home</p>');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', 'This is just a temporary website to let you know we are still here.<br />');
        $page->addContent('{##main##}', 'We will return to the internet with a full blown super-site in no time :)');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', '<a href="/register">Registrations are open</a> for guildies, please speak to an officer about it. Preferably Evo or Ani.<br />');
        $page->addContent('{##main##}', "It's all rudimentary, but you can post status updates (shouts), plan Events and do a few other things. Every user is helping with the development process as well as only used functions can really be improved upon :)<br />");
        $page->addContent('{##main##}', "Please don't use any sensitive data and only restricted API-Keys as there may still be security related bugs.");
        $page->addContent('{##main##}', '</p>');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', 'Our TS3 Server is back as well, come online and listen to the silence of loneliness on ts3.aniware.de!!');
        $page->addContent('{##main##}', '</p>');
    }
    
}
$home = new Home();
$home->initEnv();
