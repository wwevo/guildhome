<?php

class Gw2Api_Members_Model extends Gw2Api_Abstract {
    
    private $id = null;
    private $account_name = null;
    private $guild_rank = null;
    private $joined = null;
    private $api_key = null;
    private $guild_id = null;

    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Members_Model'] = $this;
    }

    function getGuildId() {
        return $this->guild_id;
    }

    function getApiKey() {
        return $this->api_key;
    }

    function getId() {
        return $this->id;
    }

    function getAccountName() {
        return $this->account_name;
    }

    function getGuildRank() {
        return $this->guild_rank;
    }

    function getJoined() {
        return $this->joined;
    }

    function setGuildId($guild_id) {
        $this->guild_id = $guild_id;
        return $this;
    }

    function setApiKey($api_key) {
        $this->api_key = $api_key;
        return $this;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setAccountName($account_name) {
        $this->account_name = $account_name;
        return $this;
    }

    function setGuildRank($guild_rank) {
        $this->guild_rank = $guild_rank;
        return $this;
    }

    function setJoined($joined) {
        $this->joined = $joined;
        return $this;
    }

    protected function isValid() {
        return true;
    }

    public function save() {
        $member_id = $this->getId();
        $guild_id = $this->getGuildId();
        $account_name = $this->getAccountName();
        $joined = $this->getJoined();
        $guild_rank = $this->getGuildRank();

        $db = db::getInstance();
        $memberObject = Gw2Api_Members_Model::getMemberObjectByAccountName($account_name);
        if (false !== $memberObject) {
            $sql = "UPDATE gw2api_members SET joined = '$joined', guild_rank = '$guild_rank' WHERE account_name = '$account_name';";
            if ($db->query($sql) === false) {
            }
            $member_id = $memberObject->getId();
        } else {
            $sql = "INSERT INTO gw2api_members (account_name, joined, guild_rank) VALUES ('$account_name', '$joined', '$guild_rank');";
            if ($db->query($sql) === false) {
            }
            $member_id = $db->insert_id;
        }
        $sql = "SELECT * FROM gw2api_guild_member_mapping WHERE member_id = $member_id AND guild_id = $guild_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            return true;
        }
        $sql = "INSERT INTO gw2api_guild_member_mapping (member_id, guild_id) VALUES ($member_id, $guild_id);";
        if ($db->query($sql) === false) {
            return false;
        }
        return true;
    }

    static function getMemberObjectByAccountName($account_name) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_members WHERE account_name = '$account_name';";
        if (($query = $db->query($sql)) !== false AND $query->num_rows == 1) {
            $member_data_row = $query->fetch_object();
            $memberObject = new Gw2Api_Members_Model();
            $memberObject->setId($member_data_row->id)->setJoined($member_data_row->joined)->setAccountName($member_data_row->account_name)->setGuildRank($member_data_row->guild_rank);
            return $memberObject;
        }
        return false;
    }

    function fetchMemberObjectsByGuildId($guild_id) {
        $guild_members = $this->gw2apiRequest("/v2/guild/$guild_id/members", $this->getApiKey());
        $membersObject_collection = [];
        foreach ($guild_members as $member_data) {
            $memberObject = new Gw2Api_Members_Model();
            $memberObject->setAccountName($member_data['name']);
            $memberObject->setGuildRank($member_data['rank']);
            $memberObject->setJoined($member_data['joined']);
            $membersObject_collection[] = $memberObject;
        }
        return (array) $membersObject_collection;
    }
    
    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS gw2api_members, gw2api_guild_member_mapping";
            $db->query($sqlDropExistingPollTables);
        }
        $sqlMembersTable = "CREATE TABLE gw2api_members (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                account_name VARCHAR(64) NOT NULL,
                guild_rank VARCHAR(16) NOT NULL,
                joined DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        if (false === $db->query($sqlMembersTable)) {
            return false;
        }            
        $sqlMappingTable = "CREATE TABLE `gw2api_guild_member_mapping` (
            `guild_id` INT(11) NOT NULL,
            `member_id` INT(11) NOT NULL,
            PRIMARY KEY (`guild_id`, `member_id`));";
        if (false === $db->query($sqlMappingTable)) {
            return false;
        }            
        return true;
    }

}
