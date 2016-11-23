<?php

class Gw2Api_Accounts_View {
    
    /**
     * Displays a prettyButton which will start the import procedure on account
     * data. Requires a 'Gw2Api_Keys_Model' Object
     * 
     * @param   Gw2Api_Keys_Model   $gw2_api_key    accepts 'Gw2Api_Keys_Model' Objects
     * @param   type                $target_url     URL to redirect to after form submit
     * @return type
     */
    public static function getImportAccountForm(Gw2Api_Keys_Model $gw2_api_key, $target_url = null) {
        $gw2_api_key_id = $gw2_api_key->getId();
        return View::createPrettyButtonForm("/gw2api/account/import/$gw2_api_key_id", $target_url, "import Account-Data");
    }

    /**
     * Displays a simple list of available account-data. Expects an array
     * 
     * @param   type    $accountObject_collection   contains an array with 'Gw2Api_Account()' objects to display
     * @return  \View
     */
    function listAccountDataView($accountObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', '<table>');
        if (is_array($accountObject_collection)) {
            foreach ($accountObject_collection as $accountObject) {
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<th colspan="2">');
                $view->addContent('{##data##}', $accountObject->getAccountName());
                $view->addContent('{##data##}', '</th>');
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td class="small center">');
                $view->addContent('{##data##}', $accountObject->getCreationDate());
                $view->addContent('{##data##}', '</td>');
                $keyObject = Gw2Api_Keys_Model::getApiKeyObjectsByAccountId($accountObject->getId(), $required_scope = 'characters', $only_one = true);
                if ($keyObject !== false) {
                    $view->addContent('{##data##}', '<td class="small center">');
                    $view->addContent('{##data##}', Gw2Api_Characters_View::getImportCharactersForm($accountObject, '/gw2api/account'));
                    $view->addContent('{##data##}', '</td>');
                } else {
                    $view->addContent('{##data##}', '<td class="small center">');
                    $view->addContent('{##data##}', 'No Api-Key with "characters" scope found!');
                    $view->addContent('{##data##}', '</td>');
                }
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td colspan="2" class="small center">');
                $view->addContent('{##data##}', Gw2Api_Guilds_View::getImportGuildsForm($accountObject, '/gw2api/account'));
                $view->addContent('{##data##}', '</td>');
                
                $view->addContent('{##data##}', '</tr>');
                $guildObject_collection = Gw2Api_Guilds_Model::getGuildObjectsByAccountId($accountObject->getId());
                if (is_array($guildObject_collection)) {
                    $view->addContent('{##data##}', '<tr>');
                    $view->addContent('{##data##}', '<td colspan="2">');
                    $view->addContent('{##data##}', Gw2Api_Guilds_View::listAvailableGuildsView($guildObject_collection));
                    $view->addContent('{##data##}', '</td>');
                    $view->addContent('{##data##}', '</tr>');
                }
            }
        } else {
            $view->addContent('{##data##}', '<tr>');
            $view->addContent('{##data##}', '<th>');
            $view->addContent('{##data##}', 'no Account-data found');
            $view->addContent('{##data##}', '</th>');
            $view->addContent('{##data##}', '</tr>');
        }
        $view->addContent('{##data##}', '</table>');
        $view->replaceTags();
        return $view;
    }
}
