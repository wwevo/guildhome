<?php

class Gw2Api_Accounts_Model extends Gw2Api_Abstract implements Gw2Api_Accounts_Interface {
    private $id = null;
    private $account_id = null;
    private $account_name = null;
    private $user_id = null;
    private $creation_date = null;
    private $world = null;
    private $commander = null;
    private $api_key = null;
    private $guilds = null;

    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Accounts_Model'] = $this;
    }

    function getId() {
        return $this->id;
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

    public function getApiKey() {
        return $this->api_key;
    }

    function getGuilds() {
        return unserialize($this->guilds);
    }

    function setId($id) {
        $this->id = $id;
        return $this;
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

    public function setApiKey($api_key) {
        $this->api_key = $api_key;
        return $this;
    }

    function setGuilds($guilds) {
        $this->guilds = $guilds;
        return $this;
    }

    static function getAccountObjectsByUserId($user_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_accounts WHERE user_id = $user_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $accountObject_collection = [];
            while ($account_data_row = $query->fetch_object()) {
                $accountObject = new Gw2Api_Accounts_Model();
                $accountObject_collection[] = $accountObject->setId($account_data_row->id)->setAccountId($account_data_row->account_id)->setAccountName($account_data_row->account_name)->setUserid($account_data_row->user_id)->setCreationDate($account_data_row->creation_date)->setWorld($account_data_row->world)->setCommander($account_data_row->commander)->setGuilds($account_data_row->guilds);
            }
            return (array) $accountObject_collection;
        }
        return false;
    }

    static function getAccountObjectByAccountId($account_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_accounts WHERE account_id = '$account_id';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows == 1) {
            $account_data_row = $query->fetch_object();
            $accountObject = new Gw2Api_Accounts_Model();
            $accountObject->setId($account_data_row->id)->setAccountId($account_data_row->account_id)->setAccountName($account_data_row->account_name)->setUserid($account_data_row->user_id)->setCreationDate($account_data_row->creation_date)->setWorld($account_data_row->world)->setCommander($account_data_row->commander)->setGuilds($account_data_row->guilds);
            return $accountObject;
        }
        return false;
    }

    static function getAccountObjectById($id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_accounts WHERE id = '$id';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows == 1) {
            $account_data_row = $query->fetch_object();
            $accountObject = new Gw2Api_Accounts_Model();
            $accountObject->setId($account_data_row->id)->setAccountId($account_data_row->account_id)->setAccountName($account_data_row->account_name)->setUserid($account_data_row->user_id)->setCreationDate($account_data_row->creation_date)->setWorld($account_data_row->world)->setCommander($account_data_row->commander)->setGuilds($account_data_row->guilds);
            return $accountObject;
        }
        return false;
    }

    static function getAccountObjectByApiKey($api_key) {
        $db = db::getInstance();
        $keyObject = Gw2Api_Keys_Model::getApiKeyObjectByApiKey($api_key);
        $api_key_id = $keyObject->getId();
        $sql = "SELECT * FROM gw2api_account_key_mapping WHERE api_key_id = '$api_key_id';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows == 1) {
            $account_data_row = $query->fetch_object();
            $account_id = $account_data_row->account_id;
            $accountObject = Gw2Api_Accounts_Model::getAccountObjectById($account_id);
            return $accountObject;
        }
        return false;
    }

    /**
     * Checks if there's enough available data to start the api-fetching.
     * @return boolean
     */
    protected function isValid() {
        if ($this->getUserid() === null) {
            return false;
        }
        if ($this->getApiKey() === null) {
            return false;
        }
        return true;
    }

    public function save() {
        $user_id = $this->getUserid();
        // get account data from the api.
        $keyObject = Gw2Api_Keys_Model::getApiKeyObjectByApiKey($this->getApiKey());
        $api_key_id = $keyObject->getId();
        $api_accountinfo = $this->gw2apiRequest('/v2/account', $keyObject->getApiKey());
            $account_id = $api_accountinfo['id'];
            $account_name = $api_accountinfo['name'];
            $creation_date = $api_accountinfo['created'];
            $world = $api_accountinfo['world'];
            $commander = $api_accountinfo['commander'];
            $guilds = serialize($api_accountinfo['guilds']);
        
        $db = db::getInstance();
        $accountObject = Gw2Api_Accounts_Model::getAccountObjectByAccountId($account_id);
        if (false !== $accountObject) {
            $sql = "UPDATE gw2api_accounts SET account_id = '$account_id', account_name = '$account_name', user_id = $user_id, creation_date = '$creation_date', world = $world, guilds = '$guilds', commander = '$commander' WHERE account_id = '$account_id' AND user_id = $user_id;";
            if ($db->query($sql) === false) {
                return false;
            }
            $account_id = $accountObject->getId();
        } else {
            $sql = "INSERT INTO gw2api_accounts (account_id, account_name, user_id, creation_date, world, commander, guilds) VALUES ('$account_id', '$account_name', $user_id, '$creation_date', '$world', '$commander', '$guilds');";
            if ($db->query($sql) === false) {
                return false;
            }
            $account_id = $db->insert_id;
        }
        $sql = "SELECT * FROM gw2api_account_key_mapping WHERE api_key_id = $api_key_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            return true;
        }
        $sql = "INSERT INTO gw2api_account_key_mapping (account_id, api_key_id) VALUES ('$account_id', $api_key_id);";
        if ($db->query($sql) === false) {
            return false;
        }
        return true;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropAccountsTable = "DROP TABLE IF EXISTS gw2api_account_key_mapping, gw2api_accounts";
            $db->query($sqlDropAccountsTable);
        }
        $sqlAccountTable = "CREATE TABLE `gw2api_accounts` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `account_id` VARCHAR(100) NOT NULL,
            `account_name` VARCHAR(100) NOT NULL,
            `user_id` INT(6) UNSIGNED NOT NULL,
            `guilds` TEXT NOT NULL,
            `creation_date` TIMESTAMP NOT NULL,
            `last_updated` TIMESTAMP NOT NULL,
            `world` VARCHAR(100) NULL DEFAULT NULL,
            `commander` TINYINT NULL DEFAULT 0
        );";
        if (false === ($error = $db->query($sqlAccountTable))) {
            return false;
        }            
        $sqlMappingTable = "CREATE TABLE `gw2api_account_key_mapping` (
            `account_id` INT(11) NOT NULL,
            `api_key_id` INT(11) NOT NULL,
            PRIMARY KEY (`account_id`, `api_key_id`)
        );";
        return $db->query($sqlMappingTable);
    }
}
