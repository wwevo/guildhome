<?php

class Gw2Api_Account_Model extends Gw2Api_Abstract implements Gw2Api_Account_Interface {

    private $account_name = null;
    private $api_key_id = null;
    private $userid = null;
    
    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Account_Model'] = $this;
    }

    public function getAccountName() {
        return $this->account_name;
    }

    public function getApiKeyId() {
        return $this->api_key_id;
        
    }

    public function getUserId() {
        return $this->userid;
    }

    public function setAccountName($account_name) {
        $this->account_name = $account_name;
        return $this;
    }

    public function setApiKeyId($api_key_id) {
        $this->api_key_id = $api_key_id;
        return $this;
    }

    public function setUserId($userid) {
        $this->userid = $userid;
        return $this;
    }

    protected function isValid() {
        // TODO: check 
        return true;
    }

    public function save() {
        $account_name = $this->getAccount_name();
        $userid = $this->getUserId();
        $db = db::getInstance();
        $sql = "INSERT INTO api_accounts (account_name, userid) VALUES ('$account_name', $userid);";
        if ($db->query($sql) === false) {
            // TODO: Error handling
            return false;
        }
        $account_id = $db->insert_id;
        $api_key_id = $this->getApiKeyId();
        $sql = "INSERT INTO api_accounts (key_id, account_id) VALUES ('$api_key_id', $account_id);";
        if ($db->query($sql) === false) {
            // TODO: Error handling
            return false;
        }
        return true;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_accounts, api_key_account_mapping";
            $db->query($sqlDropExistingPollTables);
        }
        $api_accountsTable = "CREATE TABLE api_accounts (id int(11) NOT NULL AUTO_INCREMENT, account_name varchar(64) NOT NULL, userid int(11) NOT NULL, PRIMARY KEY (id, userid))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($api_accountsTable);
        $api_key_account_mappingTable = "CREATE TABLE api_key_account_mapping (key_id varchar(64) NOT NULL REFERENCES api_keys(id), account_id int(11) NOT NULL REFERENCES api_accounts(id), PRIMARY KEY (key_id, account_id))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($api_key_account_mappingTable);
    }
}
