<?php

class Gw2Api_Account_Model extends Gw2Api_Abstract implements Gw2Api_Account_Interface {
    private $account_id = null;
    private $account_name = null;
    private $user_id = null;
    private $creation_date = null;
    private $world = null;
    private $commander = null;

    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Account_Model'] = $this;
    }

    public function getAccountId() {
        return $this->account_id;
    }

    public function getAccountName() {
        return $this->account_name;
    }

    public function getUserid() {
        return $this->user_id;
    }

    public function getCreationDate() {
        return $this->creation_date;
    }

    public function getWorld() {
        return $this->world;
    }

    public function getCommander() {
        return $this->commander;
    }

    public function setAccountId($account_id) {
        $this->account_id = $account_id;
        return $this;
    }

    public function setAccountName($account_name) {
        $this->account_name = $account_name;
        return $this;
    }

    public function setUserid($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    public function setCreationDate($creation_date) {
        $this->creation_date = $creation_date;
        return $this;
    }

    public function setWorld($world) {
        $this->world = $world;
        return $this;
    }

    public function setCommander($commander) {
        $this->commander = $commander;
        return $this;
    }

    protected function isValid() {
        // TODO: check
        return true;
    }

    public function save() {
        $account_name = $this->getAccount_name();
        $user_id = $this->getUserId();
        $db = db::getInstance();
        // TODO: fix query
        $sql = "INSERT INTO api_accounts (account_name, user_id) VALUES ('$account_name', $user_id);";
        if ($db->query($sql) === false) {
            // TODO: Error handling
            return false;
        }
        $account_id = $db->insert_id;
        $api_key_id = $this->getApiKeyId();
        // TODO: fix query
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
            $sqlDropAccountsTable = "DROP TABLE IF EXISTS gw2api_account_key_mapping, gw2api_account";
            $db->query($sqlDropAccountsTable);
        }
        $sqlAccountTable = "CREATE TABLE `gw2api_account` (`account_id` VARCHAR(100) NOT NULL,`account_name` VARCHAR(100) NOT NULL,
            `user_id` INT(6) UNSIGNED NOT NULL,`creation_date` TIMESTAMP NOT NULL,`world` VARCHAR(100) NULL DEFAULT NULL,`commander` TINYINT NULL DEFAULT 0,
            PRIMARY KEY (`account_id`));";
        $db->query($sqlAccountTable);
        $sqlMappingTable = "CREATE TABLE `gw2api_account_key_mapping` (`account_id` VARCHAR(100) NOT NULL,`api_key` VARCHAR(72) NOT NULL,
            PRIMARY KEY (`account_id`, `api_key`),INDEX `gw2apiKTAM_toKey_idx` (`api_key` ASC),
            CONSTRAINT `gw2apiKTAM_toAccount` FOREIGN KEY (`account_id`) REFERENCES `gw2api_account` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE);";
        $db->query($sqlMappingTable);
    }
}
