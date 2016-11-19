<?php

class Gw2Api_Characters {

    public $model;
    public $view;
    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {
        
    }
    
    function __construct() {
        $this->model = new Gw2Api_Characters_Model();
        $this->view = new Gw2Api_Characters_View();
    }
}
$init_env = new Gw2Api_Characters();
$init_env->initEnv();
unset($init_env);
