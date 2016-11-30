<?php

class Activity_Event_Tags {
    // start controller
    
    public $model;
    public $view;

    function initEnv() {
        Toro::addRoute(["/activity/event/tags/:alpha/:number" => "Activity_Event_Tags"]);
        
        Env::registerHook('activity_event_form_hook', array(new Activity_Event_Tags_View(), 'getTagsFormView'));
        Env::registerHook('activity_event_view_infuse_tags', array(new Activity_Event_Tags_View(), 'tagInfuser'));
    }

    function __construct() {
        $this->model = new Activity_Event_Tags_Model();
        $this->view = new Activity_Event_Tags_View();
    }


    function post($alpha, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($alpha) {
            case 'update' :
                if (isset($env->post('activity')['submit']['tags'])) {
                    $this->model->saveTags($id);
                    header("Location: /activity/event/update/" . $id);
                    exit;
                }
                break;
        }
    }

}
$init_env = new Activity_Event_Tags();
$init_env->initEnv();
unset($init_env);