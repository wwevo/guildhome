<?php

class Gw2Api_Guilds_View {
    
    public static function getImportGuildsForm(Gw2Api_Accounts_Model $account, $target_url = null) {
        $id = $account->getId();
        return View::createPrettyButtonForm("/gw2api/guilds/import/$id", $target_url, "import Account-Guilds");
    }

    static function listAvailableGuildsView($guildsObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', '<table>');
        if (is_array($guildsObject_collection)) {
            foreach ($guildsObject_collection as $guildObject) {
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<th>');
                $view->addContent('{##data##}', $guildObject->getName());
                $view->addContent('{##data##}', '</th>');
                $view->addContent('{##data##}', '</tr>');
            }
        } else {
            $view->addContent('{##data##}', '<tr>');
            $view->addContent('{##data##}', '<th>');
            $view->addContent('{##data##}', 'no Guild-data found');
            $view->addContent('{##data##}', '</th>');
            $view->addContent('{##data##}', '</tr>');
        }
        $view->addContent('{##data##}', '</table>');
        $view->replaceTags();
        return $view;
    }
  
}
