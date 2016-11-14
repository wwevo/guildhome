<?php

abstract class Gw2Api_Model implements IDatabaseModel {

    protected abstract function createDatabaseTablesByType(boolean $overwriteIfExists);
    public function createDatabaseTables($overwriteIfExists) {
        return $this->createDatabaseTablesByType($overwriteIfExists);
    }

    
}
