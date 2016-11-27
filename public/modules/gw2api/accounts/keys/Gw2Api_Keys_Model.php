<?php

class Gw2Api_Keys_Model extends Gw2Api_Abstract implements Gw2Api_Keys_Interface {
    private $user_id = null;
    private $api_key = null;
    private $api_key_name = '';
    private $api_key_permissions = [];
    
    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Keys_Model'] = $this;
    }

    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getApiKey() {
        return $this->api_key;
    }

    public function getApiKeyName() {
        return $this->api_key_name;
    }

    public function getApiKeyPermissions() {
        return unserialize($this->api_key_permissions);
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    public function setApiKey($api_key) {
        $this->api_key = $api_key;
        return $this;
    }

    public function setApiKeyName($api_key_name) {
        $this->api_key_name = $api_key_name;
        return $this;
    }

    public function setApiKeyPermissions($api_key_permissions) {
        $this->api_key_permissions = $api_key_permissions;
        return $this;
    }

    function isValid() {
        $msg = Msg::getInstance();
        $api_key = $this->getApiKey();
        $user_id = $this->getUserId();
        if ($user_id != Login::currentUserID()) {
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

    /**
     * Fetches all Gw2api_Keys associated with the given $user_id
     * 
     * @param   type    $user_id
     * @return  array of 'Gw2api_Keys_Model' Objects
     * @return  false
     */
    static function getApiKeyObjectsByUserId($user_id, $required_scope = null) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_key WHERE user_id = $user_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $keyObject_collection = [];
            while ($key_mapping_row = $query->fetch_object()) {
                $api_key_id = $key_mapping_row->id;
                $keyObject = Gw2Api_Keys_Model::getApiKeyObjectByApiKeyId($api_key_id);
                if ($required_scope === null) {
                    $keyObject_collection[] = $keyObject;
                } else {
                    if (in_array($required_scope, $keyObject->getApiKeyPermissions())) {
                        $keyObject_collection[] = $keyObject;
                    }
                }
            }
//            while ($api_key_row = $query->fetch_object()) {
//                $keyObject = new Gw2Api_Keys_Model();
//                $keyObject_collection[] = $keyObject->setId($api_key_row->id)->setApiKey($api_key_row->api_key)->setApiKeyName($api_key_row->api_key_name)->setUserId($api_key_row->user_id)->setApiKeyPermissions($api_key_row->api_key_permissions);
//            }
            return (array) $keyObject_collection;
        }
        return false;
    }

    /**
     * Fetches one Gw2api_Key associated with the given $api_key
     * 
     * @param   type    $api_key
     * @return  one instance of 'Gw2api_Keys_Model' Object
     * @return  false
     */
    static function getApiKeyObjectByApiKey($api_key) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_key WHERE api_key = '$api_key';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $api_key_row = $query->fetch_object();
            $keyObject = new Gw2Api_Keys_Model();
            $keyObject->setId($api_key_row->id)->setApiKey($api_key_row->api_key)->setApiKeyName($api_key_row->api_key_name)->setUserId($api_key_row->user_id)->setApiKeyPermissions($api_key_row->api_key_permissions);
            return $keyObject;
        }
        return false;
    }
 
    /**
     * Fetches one Gw2api_Key associated with the given $api_key_id
     * 
     * @param   type    $api_key_id
     * @return  one instance of 'Gw2api_Keys_Model' Object
     * @return  false
     */
    static function getApiKeyObjectByApiKeyId($api_key_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_key WHERE id = $api_key_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows == 1) {
            $api_key_row = $query->fetch_object();
            $keyObject = new Gw2Api_Keys_Model();
            $keyObject->setId($api_key_row->id)->setApiKey($api_key_row->api_key)->setApiKeyName($api_key_row->api_key_name)->setUserId($api_key_row->user_id)->setApiKeyPermissions($api_key_row->api_key_permissions);
            return $keyObject;
        }
        return false;
    }
 
    /**
     * returns one or more Gw2api_Keys_Model()s
     * 
     * @param   type        $account_id         website-db account_id
     * @param   type        $required_scope     only return keyObjects with this scope
     * @param   type        $only_one           return first keyObject only
     * @return  collection of Gw2_Keys_Model()
     * @return  Gw2_Keys_Model()
     */
    static function getApiKeyObjectsByAccountId($account_id, $required_scope = null, $only_one = false) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_account_key_mapping WHERE account_id = '$account_id';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $keyObject_collection = [];
            while ($key_mapping_row = $query->fetch_object()) {
                $api_key_id = $key_mapping_row->api_key_id;
                $keyObject = Gw2Api_Keys_Model::getApiKeyObjectByApiKeyId($api_key_id);
                if ($required_scope === null) {
                    $keyObject_collection[] = $keyObject;
                } else {
                    if (in_array($required_scope, $keyObject->getApiKeyPermissions())) {
                        if ($only_one) {
                            return $keyObject;
                        } else {
                            $keyObject_collection[] = $keyObject;
                        }
                    }
                }
            }
            if (empty($keyObject_collection)) {
                return false;
            }
            return (array) $keyObject_collection;
        }
        return false;
    }

    public function save() {
        $api_key = $this->getApiKey();
        $user_id = $this->getUserId();
        // get the scopes from the api. We know this will work because it
        // must be a valid key at this point
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $api_key);
            $api_key_permissions = serialize($api_tokeninfo['permissions']);
            $api_key_name = $api_tokeninfo['name'];

        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_key WHERE api_key = '$api_key' AND user_id = $user_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $sql = "UPDATE gw2api_key SET api_key = '$api_key', api_key_name = '$api_key_name', userid = $user_id, api_key_permissions = '$api_key_permissions' WHERE api_key = '$api_key' AND user_id = $user_id;";
        } else {
            $sql = "INSERT INTO gw2api_key (api_key, api_key_name, user_id, api_key_permissions) VALUES ('$api_key', '$api_key_name', $user_id, '$api_key_permissions');";
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
        $sqlKeysTable = "CREATE TABLE `gw2api_key` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT(6) UNSIGNED NOT NULL,
            `api_key` VARCHAR(72) NOT NULL,
            `api_key_name` VARCHAR(45) NOT NULL,
            `api_key_permissions` TEXT NOT NULL,
            CONSTRAINT `fk_gw2apiToUser` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION);";
        $db->query($sqlKeysTable);
    }
}
