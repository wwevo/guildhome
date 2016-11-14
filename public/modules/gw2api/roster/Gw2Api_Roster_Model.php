<?php

class Gw2Api_Roster_Model extends Gw2Api_Model {
    protected function createDatabaseTablesByType(boolean $overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_roster";
            $db->query($sqlDropExistingPollTables);
        }
        $sqlShoutsTable = "CREATE TABLE `api_roster` (`account_name` varchar(64) NOT NULL,`guild_rank` varchar(16) NOT NULL,PRIMARY KEY (`account_name`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }
}
