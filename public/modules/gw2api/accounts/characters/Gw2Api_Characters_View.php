<?php

class Gw2Api_Characters_View {

    public static function getImportCharactersForm(Gw2Api_Accounts_Model $account, $target_url = null) {
        $id = $account->getId();
        return View::createPrettyButtonForm("/gw2api/characters/import/$id", $target_url, "import Account-Characters");
    }

    function listDataTableView($charactersObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', '<table>');
        if (is_array($charactersObject_collection)) {
            $view->addContent('{##data##}', '<tr>');
            $view->addContent('{##data##}', '    <th>Lvl</th>');
            $view->addContent('{##data##}', '    <th>Name</th>');
            $view->addContent('{##data##}', '    <th>Race/Gender</th>');
            $view->addContent('{##data##}', '    <th>Profession</th>');
            $view->addContent('{##data##}', '    <th>Age</th>');
            $view->addContent('{##data##}', '</tr>');

            foreach ($charactersObject_collection as $charactersObject) {
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td class="small center">');
                $view->addContent('{##data##}', $charactersObject->getLevel());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '<td>');
                $view->addContent('{##data##}', $charactersObject->getName());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '<td class="small center">');
                $view->addContent('{##data##}', $charactersObject->getRace());
                $view->addContent('{##data##}', '<br />');
                $view->addContent('{##data##}', $charactersObject->getGender());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '<td class="center">');
                $view->addContent('{##data##}', $charactersObject->getProfession());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '<td class="small right">');
                $view->addContent('{##data##}', 'created @ ' . $charactersObject->getCreationDate());
                $view->addContent('{##data##}', '<br />');
                $view->addContent('{##data##}', $charactersObject->getAge() . ' days, b-Day in ' . $charactersObject->getBirthdayIn() . ' days');
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
