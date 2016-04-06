<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Identity
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Identity {
    function initEnv () {}
    
    function create_tables() {
        $db = db::getInstance();
        $sql = "CREATE TABLE identities (
            id INT(6) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
            user_id INT(6),
            identity_id INT(6),
            name VARCHAR(64)
        )";
        $result = $db->query($sql);
        echo $sql;
    }
    
    function get() {}
    
    public function getCurrentIdentity() {
        return 0;
    }
    
    function getCurrentAvatar() {
        $settings = new Settings();
        $avatar = $settings->getSettingByKey('avatar');
        return $avatar;
    }

    function getAvatarByUserId($user_id) {
        $settings = new Settings();
        $avatar = $settings->getSettingByKey('avatar', $user_id);
        if ($avatar !== false AND !empty($avatar)) {
            return $avatar;
        }
        return '/themes/eol/images/guild_avatar.png';
    }
    
    function getIdentityById($user, $identity) {
        $profile = new Profile();
        if ($identity == 0) {
            $user = $profile->getUsers($user)[0];
        }
        return $user->username;
    }
    
}
$identity = new Identity();
$identity->initEnv();
