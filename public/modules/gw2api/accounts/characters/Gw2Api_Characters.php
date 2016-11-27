<?php

class Gw2Api_Characters {

    public $model;
    public $view;
    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {
        Toro::addRoute(["/gw2api/characters" => "Gw2Api_Characters"]);
        Toro::addRoute(["/gw2api/characters/:string/:number" => "Gw2Api_Characters"]);
    }
    
    function __construct() {
        $this->model = new Gw2Api_Characters_Model();
        $this->view = new Gw2Api_Characters_View();
    }

    public function post($action = null, $account_id = null) {
        $env = Env::getInstance();
        switch ($action) {
            default :
                break;
            case "import" :
                $keyObject = Gw2Api_Keys_Model::getApiKeyObjectsByAccountId($account_id, $required_scope = 'characters', $only_one = true);
                if ($keyObject === false) {
                    break;
                }
                $charactersObject = new Gw2Api_Characters_Model();
                $charactersObject_collection = $charactersObject->fetchCharacterObjectsByApiKey($keyObject->getApiKey());
                foreach ($charactersObject_collection as $charactersObject) {
                    $charactersObject->attemptSave();
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
$init_env = new Gw2Api_Characters();
$init_env->initEnv();
unset($init_env);
