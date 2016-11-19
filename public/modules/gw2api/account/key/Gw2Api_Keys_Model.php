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
        $key = $this->getApiKey();
        $userid = $this->getUserId();
        if ($userid != Login::currentUserID()) {
            return false;
        }
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $key);
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
        $sql = "SELECT * FROM api_keys WHERE userid = $userid;";
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
        $sql = "SELECT FROM api_keys WHERE api_key = '$api_key';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $sql = "UPDATE api_keys SET api_key = '$api_key', api_key_name = '$api_key_name', userid = $userid WHERE api_key = '$api_key' AND userid = $userid;";
        } else {
            $sql = "INSERT INTO api_keys (api_key, api_key_name, userid) VALUES ('$api_key', '$api_key_name', $userid);";
        }
        if ($db->query($sql) !== false) {
            return true;
        }
        return false;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_keys";
            $db->query($sqlDropExistingPollTables);
        }
        $api_keysTable = "CREATE TABLE api_keys (
                id int(11) NOT NULL AUTO_INCREMENT,
                api_key varchar(72) NOT NULL,
                userid int(11) NOT NULL,
                api_key_name varchar(32),
            PRIMARY KEY (id, userid)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($api_keysTable);
    }

}
