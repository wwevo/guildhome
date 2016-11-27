<?php

class Gw2Api {
    
    function initEnv() {
        Toro::addRoute(["/gw2api" => "Gw2Api"]);
    }

    public function get() {
        if (!Login::isLoggedIn()) {
            return false;
        }
        header("Location: /gw2api/account");
        exit;
    }

    public function post() {
        $this->get();
    }
}
$init_env = new Gw2Api();
$init_env->initEnv();
unset($init_env);