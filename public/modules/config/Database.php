<?php

class Database {

    function initEnv() {
        Toro::addRoute(["/dbsetup" => 'database']);
    }

    function get() {
        $page = Page::getInstance();
        if (!Login::isAdmin()) {
            $page->setContent('{##main##}', "Try again, guy, you are no admin!");
            return false;
        }

        $page->setContent('{##main##}', "");
        $page->addContent('{##main##}', View::createPrettyButtonForm("/dbsetup", null, "Start DB setup!"));
    }

    function post() {
        if (!Login::isAdmin()) {
            return false;
        }
//        var_dump($_SESSION['dbconfig']);
        if (isset($_SESSION['dbconfig'])) {
            foreach ($_SESSION['dbconfig'] as $model) {
                $model->createDatabaseTables((boolean) true);
            }
        }
    }

}

$init = new Database();
$init->initEnv();
unset($init);
