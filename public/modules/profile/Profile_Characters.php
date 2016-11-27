<?php

class Profile_Characters {
    
    function initEnv() {
        Toro::addRoute(["/profile/:alpha/characters" => 'Profile_Characters']);
    }

    function get($user_name = NULL) {
        $page = Page::getInstance();
        
        if ($user_name == Login::currentUsername()) {
            $page->setContent('{##main##}', '<h2>Characters</h2>');

            $accountObject = new Gw2Api_Accounts_Model();
            $accountObject_collection = $accountObject->getAccountObjectsByUserId(Login::currentUserID());
            if (!is_array($accountObject_collection)) {
                return false;
            }
            foreach ($accountObject_collection as $accountObject) {
                $charactersObject = new Gw2Api_Characters();
                $charactersObject_collection = $charactersObject->model->getCharacterDataByAccountId($accountObject->getAccountId());
                Page::getInstance()->addContent('{##main##}', '<h4>' . $accountObject->getAccountName() . '</h4>');
                Page::getInstance()->addContent('{##main##}', $charactersObject->view->listDataTableView($charactersObject_collection));
            }
        }
    }   
}
$init_env = new Profile_Characters();
$init_env->initEnv();
unset($init_env);
