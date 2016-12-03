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
        if (isset($env->post('activity')['tags']) && is_array($env->post('activity')['tags'])) {
            $post_data = $env->post('activity')['tags'];
            $action = key($post_data['submit']);
//            echo "<pre>";
//            var_dump($post_data);
//            var_dump($action);
//            echo "</pre>";
        }

        switch ($alpha) {
            case 'update' :
                if ($action == 'create') {
                    $tagObject = new Activity_Event_Tags_Model();
                    $uxtime = time();
                    $tagObject->setCreationDate($uxtime)->setName($post_data['name'])->setUserId(Login::currentUserID());
                    $tagObject->save($id);
                }
                if ($action == 'save') {
                    $this->model->toggleActivation($id);
                }
                if ($action == 'select') {
                    echo "<pre>";
                    var_dump($post_data);
                    var_dump($action);
                    echo "</pre>";
                }
                if ($action == 'remove') {
                    echo "<pre>";
                    var_dump($post_data);
                    var_dump($action);
                    echo "</pre>";
                }
                break;
        }
        exit;
        header("Location: /activity/event/update/" . $id);
        exit;
    }

}
$init_env = new Activity_Event_Tags();
$init_env->initEnv();
unset($init_env);