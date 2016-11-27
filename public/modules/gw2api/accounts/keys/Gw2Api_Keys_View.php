<?php

class Gw2Api_Keys_View {
    
    /**
     * Displays simple form to add an API-Key 
     * 
     * @param   type    $target_url     contains ULR to redirect to after submitting the form,
     * @return \View
     */
    function getNewApiKeyFormView($target_url = '') {
        $env = Env::getInstance();

        $view = new View();
        $view->setTmpl($view->loadFile('/views/gw2api/account/add_api_key_form.php'), array(
            '{##form_action##}' => '/gw2api/account/key/',
            '{##target_url##}' => $target_url,
            '{##update_setting_validation##}' => Msg::getInstance()->fetch('add_api_key_form'),
            '{##setting_submit_text##}' => 'add Api-Key',
        ));
        $setting_value = isset($env->post('gw2_api_account_add_key')['api_key']) ? $env->post('gw2_api_account_add_key')['api_key'] : '';
        $view->addContent('{##setting_value##}', $setting_value);

        $view->replaceTags();
        return $view;
    }

    /**
     * Displays a simple list of available API-Keys
     * 
     * @param   type    $keyObject_collection   contains an array with 'Gw2Api_Keys()' to display
     * @return  \View
     */
    function listDataTableView($keyObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        if (is_array($keyObject_collection)) {
            foreach ($keyObject_collection as $keyObject) {
                $view->addContent('{##data##}', '<table>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<th colspan="2">');
                $view->addContent('{##data##}', $keyObject->getApiKeyName());
                $view->addContent('{##data##}', '</th>');
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td colspan="2" class="small center">');
                $view->addContent('{##data##}', $keyObject->getApiKey());
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '<tr>');
                $view->addContent('{##data##}', '<td>');
                $view->addContent('{##data##}', '<ul><li>' . implode('</li><li>', $keyObject->getApiKeyPermissions()) . '</li></ul>');
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '<td class="right">');
                $view->addContent('{##data##}', Gw2Api_Accounts_View::getImportAccountForm($keyObject, '/gw2api/account'));
                $view->addContent('{##data##}', '</td>');
                $view->addContent('{##data##}', '</tr>');
                $view->addContent('{##data##}', '</table>');
            }
        } else {
            $view->addContent('{##data##}', '<table>');
            $view->addContent('{##data##}', '<tr>');
            $view->addContent('{##data##}', '<th>');
            $view->addContent('{##data##}', 'no Api-Keys found');
            $view->addContent('{##data##}', '</th>');
            $view->addContent('{##data##}', '</tr>');
            $view->addContent('{##data##}', '</table>');
        }
        $view->replaceTags();
        return $view;
    }

}
