<?php

class Gw2Api_Characters_Model extends Gw2Api_Abstract {

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingCharactersTables = "DROP TABLE IF EXISTS gw2api_characters";
            $db->query($sqlDropExistingCharactersTables);
        }
        $sqlCharactersTable = "CREATE TABLE `gw2api_characters` (`eol_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,`account_id` VARCHAR(100) NOT NULL,
            `creation_date` TIMESTAMP NOT NULL,`name` VARCHAR(100) NULL,`gender` VARCHAR(45) NULL,`race` VARCHAR(45) NOT NULL,`profession` VARCHAR(45) NOT NULL,
            PRIMARY KEY (`account_id`, `creation_date`, `race`, `profession`),CONSTRAINT `fk_gw2apiCharToGw2apiAccount` FOREIGN KEY (`account_id`)
            REFERENCES `guildportal`.`gw2api_account` (`account_id`) ON DELETE CASCADE ON UPDATE NO ACTION);";
        $db->query($sqlCharactersTable);
    }

    protected function isValid() {
        
    }

    public function save() {
        
    }

}
