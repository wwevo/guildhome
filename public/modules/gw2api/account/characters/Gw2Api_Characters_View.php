<?php

class Gw2Api_Characters_View {

    public static function getImportAccountForm(Gw2Api_Characters_Model $account, $target_url = null) {
        $account_id = $account->getId();
        if ($target_url !== null) {
            return View::createPrettyButtonForm("/gw2api/characters/import/$account_id", $target_url, "import Account-Data");
        }
        return View::createPrettyButtonForm("/gw2api/characters/import/$account_id", null, "import Account-Data");
    }

    function listCharactersDataView($charactersObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', '<table>');
        if (is_array($charactersObject_collection)) {
            foreach ($charactersObject_collection as $charactersObject) {
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<th colspan="2">');
                $view->addContent('{##data##}', $charactersObject->getCharacterName());
                $view->addContent('{##data##}', '</th>');
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td colspan="2" class="small center">');
                $view->addContent('{##data##}', $charactersObject->getCreationDate());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '</tr>');
            }
        } else {
            $view->addContent('{##data##}', '<tr>');
            $view->addContent('{##data##}', '<th>');
            $view->addContent('{##data##}', 'no Character-data found');
            $view->addContent('{##data##}', '</th>');
            $view->addContent('{##data##}', '</tr>');
        }
        $view->addContent('{##data##}', '</table>');
        $view->replaceTags();
        return $view;
    }
    
}
