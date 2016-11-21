<?php

class Gw2Api_Account {

    public $model;
    public $view;

    function initEnv() {
        Toro::addRoute(["/gw2api/account" => "Gw2Api_Account"]);
        Toro::addRoute(["/gw2api/account/:string/:number" => "Gw2Api_Account"]);
    }

    function __construct() {
        $this->model = new Gw2Api_Account_Model();
        $this->view = new Gw2Api_Account_View();
    }

    /*
     * get() will be called by the Toro class if a route is met for this class.
     * routes will probbly be set in this classes initEnv(), they can be set
     * from anywhere in the project though
     */

    public function get() {
        if (!Login::isLoggedIn()) {
            return false;
        }
        // check for api keys
        $keyObject = new Gw2Api_Keys();
        $keyObject_collection = $keyObject->model->getApiKeysByUserId(Login::currentUserID());
        Page::getInstance()->addContent('{##main##}', '<h3>Available Keys</h3>');
        Page::getInstance()->addContent('{##main##}', $keyObject->view->listApiKeysView($keyObject_collection));
        Page::getInstance()->addContent('{##main##}', $keyObject->view->getNewApiKeyFormView('/gw2api/account'));
        
        $accountObject = new Gw2Api_Account();
        $accountObject_collection = $accountObject->model->getAccountObjectsByUserId(Login::currentUserID());
        Page::getInstance()->addContent('{##main##}', '<h3>Available Accounts</h3>');
        Page::getInstance()->addContent('{##main##}', $accountObject->view->listAccountDataView($accountObject_collection));
        
        if (is_array($accountObject_collection)) {
            Page::getInstance()->addContent('{##main##}', '<h3>Available Characters</h3>');
            foreach ($accountObject_collection as $accountObject) {
                $charactersObject = new Gw2Api_Characters();
                $charactersObject_collection = $charactersObject->model->getCharacterDataByAccountId($accountObject->getAccountId());
                Page::getInstance()->addContent('{##main##}', '<h4>' . $accountObject->getAccountName() . '</h4>');
                Page::getInstance()->addContent('{##main##}', $charactersObject->view->listCharactersDataView($charactersObject_collection));
            }
        }
    }

    /*
     * post() will be called by the Toro class (if a route is met for this
     * class) AND (a post header is being sent by your application).
     */
    public function post($action = null, $api_key_id = null) {
        $env = Env::getInstance();
        switch ($action) {
            default :
                break;
            case "import" :
                $keyObject = new Gw2Api_Keys_Model();
                $keyObject = $keyObject->getApiKeyObjectByApiKeyId($api_key_id);
                $api_key = $keyObject->getApiKey();
                $accountObject = new Gw2Api_Account_Model();
                $accountObject->setApiKey($api_key)->setUserId(Login::currentUserID());
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

/*
 * I chose the initEnv approach because I don't know any better.
 * it's my way of having a onFirstLoad feature for my classes
 */
$init_env = new Gw2Api_Account();
$init_env->initEnv();
unset($init_env);
