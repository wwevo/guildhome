<?php

class Gw2Api_Guilds_View {
    
    public static function getImportGuildsForm(Gw2Api_Accounts_Model $account, $target_url = null) {
        $id = $account->getId();
        return View::createPrettyButtonForm("/gw2api/guilds/import/$id", $target_url, "import Account-Guilds");
    }

    static function listDataTableView($guildsObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', '<table>');
        if (is_array($guildsObject_collection)) {
            foreach ($guildsObject_collection as $guildObject) {
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td>');
                $view->addContent('{##data##}', $guildObject->getName());
                $view->addContent('{##data##}', '</td>');
                // TODO: i'd like to display this only if user is a/the guilds leader, havent found a way yet.
                $view->addContent('{##data##}', '<td class="right">');
                $view->addContent('{##data##}', Gw2Api_Members_View::getImportMembersForm($guildObject, '/gw2api/account'));
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '</tr>');
                $membersObject_collection = Gw2Api_Members_Model::getMemberObjectsByGuildId($guildObject->getId());
                if (is_array($membersObject_collection)) {
                    $view->addContent('{##data##}', '<tr>');
                    $view->addContent('{##data##}', '<td colspan="2">');
                    $view->addContent('{##data##}', Gw2Api_Members_View::listDataTableView($membersObject_collection));
                    $view->addContent('{##data##}', '</td>');
                    $view->addContent('{##data##}', '</tr>');
                }
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
