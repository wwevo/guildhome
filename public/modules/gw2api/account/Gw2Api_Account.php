<?php

class Gw2Api_Account implements Toro_Interface {

    public $model;
    public $view;
    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {
        Toro::addRoute(["/gw2api/account" => "Gw2Api_Account"]);
    }
    
    function __construct() {
        $this->model = new Gw2Api_Account_Model();
        $this->view = new Gw2Api_Account_View();
    }

    public function get() {
        
    }

    public function post() {
        
    }

}
$init_env = new Gw2Api_Account();
$init_env->initEnv();
unset($init_env);
