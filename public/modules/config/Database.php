<?php 
class Database {
    public $view;
    
    function initEnv() {
        Toro::addRoute(["/dbsetup" => 'Database']);
    }
    
    function __construct() {
        $this->view = new Database_View();
    }
    
    function get() {
        $page = Page::getInstance();
        if (!Login::isAdmin()) {
            $page->setContent('{##main##}', "Try again, guy, you are no admin!");
            return false;
        }
        $page->addContent('{##main##}', View::createPrettyButtonForm("/dbsetup", null, "Start DB setup!", $this->view->getSelectTablesFormView()));
    }
    
    function post() {
        if (!Login::isAdmin()) {
        $page = Page::getInstance();
            $page->setContent('{##main##}', "Try again, guy, you are no admin!");
            return false;
        }
        $env = Env::getInstance();
        $selected_models = $env->post('selected_models');
        if (is_array($_SESSION['dbconfig'])) {
            foreach ($_SESSION['dbconfig'] as $class_name => $model) {
                if (false !== $selected_models && is_array($selected_models) && in_array($class_name, $selected_models)) {
                    $model->createDatabaseTables((boolean) true);
                }
            }
        }
        $this->get();
    }
}

$init = new Database();
$init->initEnv();
unset($init);
