<?php

class Gw2Api_Characters_Model extends Gw2Api_Abstract {

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_characters";
            $db->query($sqlDropExistingPollTables);
        }
        $sqlShoutsTable = "CREATE TABLE api_characters (account_name varchar(64) NOT NULL,createdate timestamp NOT NULL, PRIMARY KEY (account_name))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }

    protected function isValid() {
        
    }

    public function save() {
        
    }

}
