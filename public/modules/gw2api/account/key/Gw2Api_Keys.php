<?php

class Gw2Api_Keys {

    public $model;
    public $view;
    
    function initEnv() {
        Toro::addRoute(["/gw2api/account/key" => "Gw2Api_Keys"]);
    }
    
    function __construct() {
        $this->model = new Gw2Api_Keys_Model();
        $this->view = new Gw2Api_Keys_View();
    }

    /*
     * post() will be called by the Toro class (if a route is met for this
     * class) AND (a post header is being sent by your application).
     */
    public function post() {
        $env = Env::getInstance();
        if (isset($env->post('gw2_api_account_add_key')['submit']) && false !== Login::isLoggedIn()) {
            $id = null;
            $key = $env->post('gw2_api_account_add_key')['api_key'];
            $userid = Login::currentUserID();
            $keyObject = new Gw2Api_Keys();
            $keyObject->model->setId($id)->setApiKey($key)->setUserId($userid);
            if ($keyObject->model->attemptSave()) {
                Msg::getInstance()->add('add_api_key_form', 'Key Saved!');
            }
            header("Location: " . $env->post('target_url'));
            exit;
        }
        $this->get();
    }
}
$init_env = new Gw2Api_Keys();
$init_env->initEnv();
unset($init_env);