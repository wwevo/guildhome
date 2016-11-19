<?php

class Gw2Api_Account implements Toro_Interface {

    public $model;
    public $view;

    function initEnv() {
        Toro::addRoute(["/gw2api/account" => "Gw2Api_Account"]);
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
        var_dump($keyObject_collection);
        Page::getInstance()->addContent('{##main##}', $keyObject->view->listApiKeysByUserIdView($keyObject_collection));
        Page::getInstance()->addContent('{##main##}', $keyObject->view->getNewApiKeyFormView('/gw2api/account'));
    }

    /*
     * post() will be called by the Toro class (if a route is met for this
     * class) AND (a post header is being sent by your application).
     */
    public function post() {
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
