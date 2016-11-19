<?php

class Gw2Api_Roster {

    public $model;
    public $view;
    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {
        
    }
    
    function __construct() {
        $this->model = new Gw2Api_Roster_Model();
        $this->view = new Gw2Api_Roster_View();
    }
}
$init_env = new Gw2Api_Roster();
$init_env->initEnv();
unset($init_env);
