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
        //$this->create_tables();
    }
    
    function create_tables() {
        // Dirty Setup
        $db = db::getInstance();
        $sql = "CREATE TABLE settings (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            userid INT(6) NOT NULL,
            setting VARCHAR(128),
            setting_value TEXT
        )";
        $result = $db->query($sql);
        echo $sql;
    }
    
    function get() {
        header("Location: /activities");
    }
    
    function post($key = '') {
        $env = Env::getInstance();
        $login = new Login();

        if ($login->isLoggedIn()) {
            if (isset($env->post('setting_' . $key)['submit'])) {
                $this->updateSetting($key);
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
                    WHERE userid = '" . $user_id . "' AND setting = '" . $key . "';";
        $result = $db->query($sql);

        if ($result !== false AND $result->num_rows >= 1) {
            $result_row = $result->fetch_object();
            return $result_row->setting_value;
        } else {
            return false;
        }
        
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

    function getUpdateSettingForm($key) {
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
            '{##setting_key##}' => $key,
            '{##setting_value##}' => $setting_value,
            '{##update_setting_validation##}' => $msg->fetch('update_setting_validation'),
            '{##setting_submit_text##}' => 'update',
            '{##setting_cancel_text##}' => 'reset',
        ));
        $view->replaceTags();
        return $view;
    }
    
}
$settings = new Settings();
$settings->initEnv();
