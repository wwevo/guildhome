<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Profile
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Settings {
    
    function initEnv() {
        Toro::addRoute(["/setting/:alpha" => 'Settings']);
    }
    
    function get() {
        header("Location: /activities");
    }
    
    function post($key = '') {
        $env = Env::getInstance();
        $login = new Login();

        if ($login->isLoggedIn()) {
            if (isset($env->post('setting_' . $key)['submit'])) {
                if ($this->updateSetting($key)) {
                    header("Location: " .  $env->post('target_url'));
                }
            }
        }        
    }
    
    function getSettingByKey($key, $user_id = NULL) {
        $db = db::getInstance();

        $login = new Login();
        if ($user_id === NULL) {
            $user_id = $login->currentUserID();
        }
        $sql = "SELECT *
                    FROM settings
                    WHERE userid = '$user_id' AND setting = '$key';";
        $result = $db->query($sql);

        if ($result !== false AND $result->num_rows >= 1) {
            $result_row = $result->fetch_object();
            
            if (!empty($result_row->setting_value) AND $result_row->setting_value != '') {
                return $result_row->setting_value;    
            }
        }
        return false;
    }
    
    function updateSetting($key, $value = '') {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $error = 0;
        if (empty($value)) {
            $value = $env->post('setting_' . $key)['value'];
        }
        
        if ($error == 1) {
            return false;
        }
        $db = db::getInstance();

        $login = new Login();
        $user_id = $login->currentUserID();

        $key = $db->real_escape_string(strip_tags($key));
        $value = $db->real_escape_string(strip_tags($value));

        $sql = "SELECT * FROM settings WHERE userid = '" . $user_id . "' AND setting = '" . $key . "';";
        $result = $db->query($sql);

        if ($result !== false AND $result->num_rows >= 1) {
            $sql = "UPDATE settings SET
                        setting_value = '" . $value . "'
                    WHERE userid = '" . $user_id . "' AND setting = '" . $key . "';";
        } else {
            $sql = "INSERT INTO settings (userid, setting, setting_value)
                        VALUES ('" . $user_id . "', '" . $key . "', '" . $value . "');";
        }            

        if ($db->query($sql) === true) {
            $env->clear_post('setting_' . $key);
            return true;
        }
        return false;
    }

    function getUpdateSettingForm($key, $target_url = '') {
        $env = Env::getInstance();
        $msg = Msg::getInstance();
 
        if (isset($env->post('setting_' . $key)['value'])) {
            $setting_value = $env->post('setting_' . $key)['value'];
        } else {
            $setting_value = $this->getSettingByKey($key);
        }

        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/settings/update_setting_form.php'), array(
            '{##form_action##}' => '/setting/' . $key,
            '{##target_url##}' => $target_url,
            '{##setting_key##}' => $key,
            '{##setting_value##}' => $setting_value,
            '{##update_setting_validation##}' => $msg->fetch('update_setting_validation'),
            '{##setting_submit_text##}' => 'update',
            '{##setting_cancel_text##}' => 'reset',
        ));
        $view->replaceTags();
        return $view;
    }
    
    function getTimezonePickerForm($target_url = '') {
        $env = Env::getInstance();
        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/settings/timezone_picker_form.php'), array(
            '{##form_action##}' => '/setting/timezone',
            '{##target_url##}' => $target_url,
            '{##timezone_submit_text##}' => 'pick',
        ));
        
        if (($timezone = $this->getSettingByKey('timezone')) === false) {
            $option_selected = '';
        }

        $options_list = '';
        foreach ($env->generateTimezoneList() as $option_value => $option_text) {
            $subView = new View();
            $subView->setTmpl($view->getSubTemplate('{##timezone_select_option##}'));
            $subView->addContent('{##option_value##}', $option_value);
            $subView->addContent('{##option_text##}', $option_text);
            if ($timezone == $option_value) {
                $subView->addContent('{##option_selected##}', ' selected="selected"');
            }
            $subView->replaceTags();
            $options_list .= $subView;
        }
        $view->addContent('{##timezone_select_option##}', $options_list);
        
        $view->replaceTags();
        return $view;
        
    }
    
}
$settings = new Settings();
$settings->initEnv();
