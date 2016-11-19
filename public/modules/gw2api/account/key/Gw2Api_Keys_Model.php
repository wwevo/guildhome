<?php

class Gw2Api_Keys_Model extends Gw2Api_Abstract implements Gw2Api_Key_Interface {
    private $id = null;
    private $api_key = null;
    private $userid = null;
    private $api_key_name = '';
    
    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Key_Model'] = $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getApiKey() {
        return $this->api_key;
    }

    public function getUserId() {
        return $this->userid;
    }

    public function getApiKeyName() {
        return $this->api_key_name;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setApiKey($api_key) {
        $this->api_key = $api_key;
        return $this;
    }

    public function setUserId($userid) {
        $this->userid = $userid;
        return $this;
    }

    public function setApiKeyName($api_key_name) {
        $this->api_key_name = $api_key_name;
        return $this;
    }

    function isValid() {
        $msg = Msg::getInstance();
        $api_key = $this->getApiKey();
        $userid = $this->getUserId();
        if ($userid != Login::currentUserID()) {
            return false;
        }
        if ($api_key === null || $api_key =="") {
            $msg->add('add_api_key_form', 'Nothing entered!');
            return false;
        }
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $api_key);
        if (!isset($api_tokeninfo['permissions']) OR !is_array($api_tokeninfo['permissions'])) {
            $msg->add('add_api_key_form', 'Key rejected by Gw2-Server!');
            return false;
        }
        $api_permissions = $api_tokeninfo['permissions'];
        if (!in_array('account', $api_permissions)) {
            $msg->add('add_api_key_form', "Key does not have the required 'Account' scope!");
            return false;
        }
        $blacklisted_permissions = ['inventories', 'wallet', 'builds', 'tradingpost'];
        $blacklisted_permissions_found = array_intersect($blacklisted_permissions, $api_permissions);
        if (count($blacklisted_permissions_found) != 0) {
            $msg->add('add_api_key_form', "Your Key has too many scopes enabled. Please deactivate the following ones for now: " . print_r($blacklisted_permissions_found, true));
            return false;
        }
        return true;
    }

    function getApiKeysByUserId($userid) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_key WHERE userid = $userid;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $keyObject_collection = [];
            while ($api_key_row = $query->fetch_object()) {
                $keyObject = new self;
                $keyObject->setId($api_key_row->id)->setApiKey($api_key_row->api_key)->setApiKeyName($api_key_row->api_key_name)->setUserId($api_key_row->userid);
                $keyObject_collection[] = $keyObject;
            }
            return (array) $keyObject_collection;
        }
        return false;
    }

    public function save() {
        $api_key = $this->getApiKey();
        $userid = $this->getUserId();
        $api_key_name = $this->getApiKeyName();
        $db = db::getInstance();
        $sql = "SELECT FROM gw2api_key WHERE api_key = '$api_key';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $sql = "UPDATE gw2api_key SET api_key = '$api_key', api_key_name = '$api_key_name', userid = $userid WHERE api_key = '$api_key' AND userid = $userid;";
        } else {
            $sql = "INSERT INTO gw2api_key (api_key, api_key_name, userid) VALUES ('$api_key', '$api_key_name', $userid);";
        }
        if ($db->query($sql) !== false) {
            return true;
        }
        return false;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingKeyTables = "DROP TABLE IF EXISTS gw2api_key";
            $db->query($sqlDropExistingKeyTables);
        }
        $sqlKeysTable= "CREATE TABLE `gw2api_key` (`user_id` INT(6) UNSIGNED NOT NULL,`api_key` VARCHAR(72) NOT NULL,`api_key_name` VARCHAR(45) NOT NULL,
            `api_key_perm_account` TINYINT NOT NULL DEFAULT 0,`api_key_perm_builds` TINYINT NOT NULL DEFAULT 0,
            `api_key_perm_characters` TINYINT NOT NULL DEFAULT 0,`api_key_perm_guilds` TINYINT NOT NULL DEFAULT 0,
            `api_key_perm_inventories` TINYINT NOT NULL DEFAULT 0,`api_key_perm_progression` TINYINT NOT NULL DEFAULT 0,
            `api_key_perm_pvp` TINYINT NOT NULL DEFAULT 0,`api_key_perm_tradingpost` TINYINT NOT NULL DEFAULT 0,`api_key_perm_unlocks` TINYINT NOT NULL DEFAULT 0,
            `api_key_perm_wallet` TINYINT NOT NULL DEFAULT 0,`account_id` VARCHAR(100) NULL DEFAULT NULL, PRIMARY KEY (`user_id`, `api_key`),
            CONSTRAINT `fk_gw2apiTpUser` FOREIGN KEY (`user_id`) REFERENCES `guildportal`.`users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION);";
        $db->query($sqlKeysTable);
    }
}
