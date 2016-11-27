<?php

class Gw2Api_Members {

    public $model;
    public $view;

    function initEnv() {
        Toro::addRoute(["/gw2api/members" => "Gw2Api_Members"]);
        Toro::addRoute(["/gw2api/members/:string/:number" => "Gw2Api_Members"]);
    }

    function __construct() {
        $this->model = new Gw2Api_Members_Model();
        $this->view = new Gw2Api_Members_View();
    }

    public function post($action = null, $guild_id = null) {
        $env = Env::getInstance();
        switch ($action) {
            default :
                break;
            case "import" :
                $guildObject = Gw2Api_Guilds_Model::getGuildObjectById($guild_id);
                $keyObject_collection = Gw2Api_Keys_Model::getApiKeyObjectsByUserId(Login::currentUserID(), 'guilds');
                $membersObject = new Gw2Api_Members_Model();
                foreach ($keyObject_collection as $keyObject) {
                    $membersObject_collection = $membersObject->setApiKey($keyObject->getApiKey())->fetchMemberObjectsByGuildId($guildObject->getGuildId());
                    if (is_array($membersObject_collection)) {
                        foreach ($membersObject_collection as $membersObject) {
                            $membersObject->setGuildId($guildObject->getId())->attemptSave();
                        }
                    }
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
$init_env = new Gw2Api_Members();
$init_env->initEnv();
unset($init_env);
