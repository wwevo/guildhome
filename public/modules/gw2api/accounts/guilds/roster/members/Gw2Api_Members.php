<?php

class Gw2Api_Members {

    public $model;
    public $view;
    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {
        Toro::addRoute(["/gw2api/members" => "Gw2Api_Members"]);
        Toro::addRoute(["/gw2api/members/:string/:number" => "Gw2Api_Members"]);
    }
    
    function __construct() {
        $this->model = new Gw2Api_Members_Model();
        $this->view = new Gw2Api_Members_View();
    }

    public function post($action = null, $account_id = null) {
    }
}
$init_env = new Gw2Api_Members();
$init_env->initEnv();
unset($init_env);
