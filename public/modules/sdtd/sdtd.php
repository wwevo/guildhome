<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 7dtd
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class sdtd {
    
    function initEnv() {
        Toro::addRoute(["/7dtd" => 'sdtd']);
    }
    
    function get() {
        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>7dtd</h2>');

        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', 'Just for Fun Server for EoL Members.');
        $page->addContent('{##main##}', '</p>');
        $page->addContent('{##main##}', '<p>');
        $page->addContent('{##main##}', 'Ask Evo for the Password :)');
        $page->addContent('{##main##}', '</p>');
    }
    
}
$sdtd = new sdtd();
$sdtd->initEnv();
