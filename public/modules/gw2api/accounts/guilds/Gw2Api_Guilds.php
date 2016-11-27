<?php

class Gw2Api_Guilds {

    public $model;
    public $view;
    
    function initEnv() {
        Toro::addRoute(["/gw2api/guilds" => "Gw2Api_Guilds"]);
        Toro::addRoute(["/gw2api/guilds/:string/:number" => "Gw2Api_Guilds"]);
    }
    
    function __construct() {
        $this->model = new Gw2Api_Guilds_Model();
        $this->view = new Gw2Api_Guilds_View();
    }

    /*
     * post() will be called by the Toro class (if a route is met for this
     * class) AND (a post header is being sent by your application).
     */
    public function post($action = null, $account_id = null) {
        $env = Env::getInstance();
        switch ($action) {
            default :
                break;
            case "import" :
                $guildsObject = new Gw2Api_Guilds_Model();
                $guildsObject_collection = $guildsObject->fetchGuildObjectsByAccountId($account_id);
                foreach ($guildsObject_collection as $guildsObject) {
                    $guildsObject->attemptSave();
                }
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
$init_env = new Gw2Api_Guilds();
$init_env->initEnv();
unset($init_env);