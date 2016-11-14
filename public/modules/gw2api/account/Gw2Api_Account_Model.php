<?php

class Gw2Api_Account_Model extends Gw2Api_Model {

    protected function createDatabaseTablesByType(boolean $overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_account";
            $db->query($sqlDropExistingPollTables);
        }
        $sqlShoutsTable = "CREATE TABLE `api_characters` (`account_name` varchar(64) NOT NULL,`userid` int(11) NOT NULL, PRIMARY KEY (`userid`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }

}
