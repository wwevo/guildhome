<?php

class Gw2Api implements Toro_Interface {
    
    function initEnv() {
        Toro::addRoute(["/gw2api" => "Gw2Api"]);
    }

    public function get() {
        if (!Login::isLoggedIn()) {
            return false;
        }
        Page::getInstance()->addContent('{##main##}', View::linkFab('/gw2api/account/', 'Manage Accounts'));
    }

    public function post() {
        $this->get();
    }
}
$init_env = new Gw2Api();
$init_env->initEnv();
unset($init_env);