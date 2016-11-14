<?php

class Gw2Api_Account_Model extends Gw2Api_Model {

    protected function createDatabaseTablesByType(boolean $overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_accounts, api_keys, api_key_account_mapping";
            $db->query($sqlDropExistingPollTables);
        }
        $sqlShoutsTable = "CREATE TABLE `api_accounts` (id int(11) NOT NULL AUTO_INCREMENT, `account_name` varchar(64) NOT NULL, `userid` int(11) NOT NULL, PRIMARY KEY (`id`, `userid`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
        $sqlShoutsTable = "CREATE TABLE `api_keys` (id int(11) NOT NULL AUTO_INCREMENT, `key` varchar(64) NOT NULL, `userid` int(11) NOT NULL, PRIMARY KEY (`id`, `userid`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
        $sqlShoutsTable = "CREATE TABLE `api_key_account_mapping` (`key_id` varchar(64) NOT NULL REFERENCES api_keys(id), `account_id` int(11) NOT NULL REFERENCES api_accounts(id), PRIMARY KEY (`key_id`, `account_id`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }

}
