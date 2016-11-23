<?php

class Gw2Api_Roster {

    public $model;
    public $view;

    function initEnv() {
        Toro::addRoute(["/gw2api/roster" => "Gw2Api_Roster"]);
        Toro::addRoute(["/gw2api/roster/:string/:number" => "Gw2Api_Roster"]);
    }

    function __construct() {
        $this->model = new Gw2Api_Roster_Model();
        $this->view = new Gw2Api_Roster_View();
    }

    public function get() {
        if (!Login::isLoggedIn()) {
            return false;
        }
        
    }
    
    public function post($action = null, $api_key_id = null) {
        $env = Env::getInstance();
        switch ($action) {
            default :
                break;
            case "import" :
                $keyObject = Gw2Api_Keys_Model::getApiKeyObjectByApiKeyId($api_key_id);
                $accountObject = new Gw2Api_Accounts_Model();
                $accountObject->setApiKey($keyObject->getApiKey())->setUserId(Login::currentUserID());
                if ($accountObject->attemptSave()) { }
                break;
        }
        $target_url = $env->post('redirect_url');
        if (isset($target_url) && !empty($target_url)) {
            header("Location: $target_url");
            exit;
        }
        $this->get();    
    }

}
$init_env = new Gw2Api_Roster();
$init_env->initEnv();
unset($init_env);
