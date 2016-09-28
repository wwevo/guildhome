<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of About
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Dev {

    function initEnv() {
        Toro::addRoute(["/dev/forms" => 'Dev']);
    }

    function get() {
        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Dev</h2>');
        $page->addContent('{##main##}', '<h3>Forms</h3>');

        $register = new Register();
        $page->addContent('{##main##}', '<hr />');
        $page->addContent('{##main##}', '<h3>Register</h3>');
        $page->addContent('{##main##}', '<hr />');
        $page->addContent('{##main##}', $register->getRegisterView());
        $login = new Login();
        $page->addContent('{##main##}', '<hr />');
        $page->addContent('{##main##}', '<h3>Login</h3>');
        $page->addContent('{##main##}', '<hr />');
        $page->addContent('{##main##}', $login->getLoginView());
        $page->addContent('{##main##}', '<hr />');
        $page->addContent('{##main##}', '<h3>Logout</h3>');
        $page->addContent('{##main##}', '<hr />');
        $page->addContent('{##main##}', $login->getLogoutView());
        
    }

}

$dev = new Dev();
$dev->initEnv();
