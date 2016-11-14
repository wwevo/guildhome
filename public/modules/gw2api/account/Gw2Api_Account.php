<?php

class Gw2Api_Account {

    public $model;
    public $view;
    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {
        
    }
    
    function __construct() {
        $this->model = new Gw2Api_Account_Model();
        $this->view = new Gw2Api_Account_View();
    }
}
$init_env = new Gw2Api_Account();
$init_env->initEnv();
unset($init_env);
