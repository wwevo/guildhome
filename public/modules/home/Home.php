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
        $page->addContent('{##main##}', '<p>This website is a work in progress. Expect bugs and broken stuff :)</p>');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', 'Registrations are open for guildies, please speak to an officer about it. Preferably Evo or Ani.<br />');
        $page->addContent('{##main##}', '</p>');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', '<a href="/register">Create your website account now</a> to shout out to your guildies, create events or check out your upcoming character-birthdays.<br />');
        $page->addContent('{##main##}', '</p>');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', 'Now is your chance to participate in the development process: Help us make this website great and use the opportunity to steer this thing in a direction you like.');
        $page->addContent('{##main##}', '</p>');
        $page->addContent('{##main##}', '<p>Come online and listen to the silence of loneliness on ts3.notjustfor.me!</p>');
    }
    
}
$home = new Home();
$home->initEnv();
