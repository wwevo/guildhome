<?php

class Gw2Api_Guilds_Model extends Gw2Api_Abstract implements Gw2Api_Guilds_Interface {

    private $account_id = null;
    private $id = null;
    private $guild_id = null;
    private $guild_name = null;
    private $tag = null;

    function getAccountId() {
        return $this->account_id;
    }

    function getId() {
        return $this->id;
    }

    function getGuildId() {
        return $this->guild_id;
    }

    function getName() {
        return $this->guild_name;
    }

    function getTag() {
        return $this->tag;
    }

    function setAccountId($account_id) {
        $this->account_id = $account_id;
        return $this;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setGuildId($guild_id) {
        $this->guild_id = $guild_id;
        return $this;
    }

    function setName($name) {
        $this->guild_name = $name;
        return $this;
    }

    function setTag($tag) {
        $this->tag = $tag;
        return $this;
    }

    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Guilds_Model'] = $this;
    }

    function isValid() {
    }

    static function getGuildObjectById($id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_guilds WHERE id = $id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $guild_data_row = $query->fetch_object();
                $guildObject = new Gw2Api_Guilds_Model();
                $guildObject->setId($guild_data_row->id)->setGuildId($guild_data_row->guild_id)->setName($guild_data_row->name)->setTag($guild_data_row->tag);
            return $guildObject;
        }
        return false;
    }

    static function getGuildObjectsByAccountId($account_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_account_guild_mapping WHERE account_id = $account_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $guildObject_collection = [];
            while ($guild_mapping_row = $query->fetch_object()) {
                $id = $guild_mapping_row->guilds_id;
                $guildObject = Gw2Api_Guilds_Model::getGuildObjectById($id);
                $guildObject_collection[] = $guildObject;
            }
            return (array) $guildObject_collection;
        }
        return false;
    }

    /**
     * 
     * @param   type    $account_id the internal accounts(id), not the guildwars account_id one
     * @return \Gw2Api_Guilds_Model
     */
    function fetchGuildObjectsByAccountId($account_id) {
        $account_guilds = Gw2Api_Accounts_Model::getAccountObjectById($account_id);
        foreach ($account_guilds->getGuilds() as $guild_id) {
            $guild_data = $this->gw2apiRequest('/v1/guild_details.json?guild_id=' . $guild_id);

            $guildObject = new Gw2Api_Guilds_Model();
            $guildObject->setAccountId($account_id);
            $guildObject->setGuildId($guild_data['guild_id']);
            $guildObject->setName($guild_data['guild_name']);
            $guildObject->setTag($guild_data['tag']);

            $guildObject_collection[] = $guildObject;
        }        
        return $guildObject_collection;
    }

     public function save() {
        $account_id = $this->getAccountId();
        $id = $this->getId();
        $guild_id = $this->getGuildId();
        $name = $this->getName();
        $tag = $this->getTag();

        $guildObject = Gw2Api_Guilds_Model::getGuildObjectById($id);
        $db = db::getInstance();
        if (false !== $guildObject) {
            $sql = "UPDATE gw2api_guilds SET guild_id = '$guild_id', name = '$name', tag = '$tag' WHERE id = $id;";
            if ($db->query($sql) === false) {
                return false;
            }
        } else {
            $sql = "INSERT INTO gw2api_guilds (guild_id, name, tag) VALUES ('$guild_id', '$name', '$tag');";
            if ($db->query($sql) === false) {
                return false;
            }
            $id = $db->insert_id;
        }
        $sql = "SELECT * FROM gw2api_account_guild_mapping WHERE guilds_id = $id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            return true;
        }
        $sql = "INSERT INTO gw2api_account_guild_mapping (account_id, guilds_id) VALUES ('$account_id', $id);";
        if ($db->query($sql) === false) {
            return false;
        }
        return true;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropAccountsTable = "DROP TABLE IF EXISTS gw2api_account_guild_mapping, gw2api_guilds";
            $db->query($sqlDropAccountsTable);
        }
        $sqlGuildsTable = "CREATE TABLE `gw2api_guilds` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `guild_id` VARCHAR(100) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `tag` VARCHAR(100) NOT NULL
        );";
        $db->query($sqlGuildsTable);
        $sqlMappingTable = "CREATE TABLE `gw2api_account_guild_mapping` (
            `account_id` int(11) NOT NULL,
            `guilds_id` int(11) NOT NULL,
            PRIMARY KEY (`account_id`, `guilds_id`)
        );";
        $db->query($sqlMappingTable);
    }
}
