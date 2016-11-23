<?php

class Gw2Api_Members_View {
    public static function getImportMembersForm(Gw2Api_Guilds_Model $guild, $target_url = null) {
        $id = $guild->getId();
        return View::createPrettyButtonForm("/gw2api/members/import/$id", $target_url, "import Guild-Members");
    }    

    static function listMembersDataView($membersObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', '<table>');
        if (is_array($membersObject_collection)) {
            foreach ($membersObject_collection as $membersObject) {
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<th colspan="2">');
                $view->addContent('{##data##}', $membersObject->getAccountName());
                $view->addContent('{##data##}', '</th>');
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td class="small center">');
                $view->addContent('{##data##}', $membersObject->getJoined());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '<td class="small center">');
                $view->addContent('{##data##}', $membersObject->getGuildRank());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '</tr>');
            }
        } else {
            $view->addContent('{##data##}', '<tr>');
            $view->addContent('{##data##}', '<th>');
            $view->addContent('{##data##}', 'no Members-data found');
            $view->addContent('{##data##}', '</th>');
            $view->addContent('{##data##}', '</tr>');
        }
        $view->addContent('{##data##}', '</table>');
        $view->replaceTags();
        return $view;
    }

}
