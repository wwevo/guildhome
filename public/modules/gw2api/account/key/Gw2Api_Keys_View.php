<?php

class Gw2Api_Keys_View {
    
    function getNewApiKeyFormView($target_url = '') {
        $env = Env::getInstance();

        if (isset($env->post('gw2_api_account_add_key')['api_key'])) {
            $setting_value = $env->post('gw2_api_account_add_key')['api_key'];
        } else {
            $setting_value = '';
        }

        $view = new View();
        $view->setTmpl($view->loadFile('/views/gw2api/account/add_api_key_form.php'), array(
            '{##form_action##}' => '/gw2api/account/key/',
            '{##target_url##}' => $target_url,
            '{##setting_value##}' => $setting_value,
            '{##update_setting_validation##}' => Msg::getInstance()->fetch('add_api_key_form'),
            '{##setting_submit_text##}' => 'add',
        ));
        $view->replaceTags();
        return $view;
    }

    function listApiKeysByUserIdView($keyObject_collection) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', "<p>Only valid keys with allowed scopes will be accepted (guild, account, characters)</p>");
        if (is_array($keyObject_collection)) {
            $api_keys = [];
            foreach ($keyObject_collection as $keyObject) {
                $api_keys[] = $keyObject->getApiKey();
            }
            $view->addContent('{##data##}', "<pre>\n");
            $view->addContent('{##data##}', print_r($api_keys, true));
            $view->addContent('{##data##}', '</pre>');
        } else {
            $view->addContent('{##data##}', 'no Api-Keys found');
        }
        $view->replaceTags();
        return $view;
    }

    function getApiKeyManagementView($keyObject_collection) {
        $content = $this->listApiKeysByUserIdView($keyObject_collection);
        $content .= $this->getNewApiKeyFormView();
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'), array(
            '{##data##}' => $content,
        ));
        $view->replaceTags();
        return $view;
    }
}
