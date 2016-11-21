<?php

class Gw2Api_Characters_Model extends Gw2Api_Abstract {

    private $id;
    private $eol_id;
    private $account_id;
    private $creation_date;
    private $name;
    private $gender;
    private $race;
    private $profession;

    function getId() {
        return $this->id;
    }

    function getEolId() {
        return $this->eol_id;
    }

    function getAccountId() {
        return $this->account_id;
    }

    function getCreationDate() {
        return $this->creation_date;
    }

    function getName() {
        return $this->name;
    }

    function getGender() {
        return $this->gender;
    }

    function getRace() {
        return $this->race;
    }

    function getProfession() {
        return $this->profession;
    }

    public function getApiKey() {
        return $this->api_key;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setEolId($eol_id) {
        $this->eol_id = $eol_id;
        return $this;
    }

    function setAccountId($account_id) {
        $this->account_id = $account_id;
        return $this;
    }

    function setCreationDate($creation_date) {
        $this->creation_date = $creation_date;
        return $this;
    }

    function setName($name) {
        $this->name = $name;
        return $this;
    }

    function setGender($gender) {
        $this->gender = $gender;
        return $this;
    }

    function setRace($race) {
        $this->race = $race;
        return $this;
    }

    function setProfession($profession) {
        $this->profession = $profession;
        return $this;
    }

    public function setApiKey($api_key) {
        $this->api_key = $api_key;
        return $this;
    }

    function __construct() {
        $_SESSION['dbconfig']['Gw2Api_Characters_Model'] = $this;
    }

    function getCharacterDataByAccountId($account_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_characters WHERE account_id = $account_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $charactersObject_collection = [];
            while ($characters_data_row = $query->fetch_object()) {
                $charactersObject = new self;
                $charactersObject_collection[] = $charactersObject->setId($characters_data_row->id)->setEolId($characters_data_row->eol_id)->setAccountId($characters_data_row->account_id)->setCreationDate($characters_data_row->creation_date)->setName($characters_data_row->name)->setGender($characters_data_row->gender)->setRace($characters_data_row->race)->setProfession($characters_data_row->profession);
            }
            return (array) $charactersObject_collection;
        }
        return false;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropExistingCharactersTables = "DROP TABLE IF EXISTS gw2api_characters";
            $db->query($sqlDropExistingCharactersTables);
        }
        $sqlCharactersTable = "CREATE TABLE `gw2api_characters` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `eol_id` INT UNSIGNED NOT NULL,
            `account_id` VARCHAR(100) NOT NULL,
            `creation_date` TIMESTAMP NOT NULL,
            `name` VARCHAR(100) NULL,
            `gender` VARCHAR(45) NULL,
            `race` VARCHAR(45) NOT NULL,
            `profession` VARCHAR(45) NOT NULL
        );";
        $db->query($sqlCharactersTable);
    }

    protected function isValid() {
        
    }

    public function save() {
        $api_characters = $this->gw2apiRequest('/v2/characters', $this->getApiKey());
        var_dump($api_characters); exit;

        $id = $this->getId();
        $eol_id = $this->getEolId();
        $account_id = $this->getAccountId();
        $creation_date = $this->getCreationDate();
        $name = $this->getName();
        $gender = $this->getGender();
        $race = $this->getRace();
        $profession = $this->getProfession();

            $api_key_permissions = serialize($api_tokeninfo['permissions']);
            $api_key_name = $api_tokeninfo['name'];

        $db = db::getInstance();
        $sql = "SELECT * FROM gw2api_characters WHERE account_id = $account_id AND eol_id = $eol_id;";
        if (($query = $db->query($sql)) !== false AND $query->num_rows >= 1) {
            $sql = "UPDATE gw2api_key SET creation_date = '$creation_date', name = '$name', gender = '$gender' , race = '$race' , profession = '$profession'  WHERE account_id = $account_id AND eol_id = $eol_id;";
        } else {
            $sql = "INSERT INTO gw2api_key (eol_id, account_id, creation_date, name, gender, race, profession) VALUES ('$eol_id', '$account_id', $creation_date, '$name', '$gender', '$race', '$profession');";
        }
        if ($db->query($sql) !== false) {
            return true;
        }
        return false;
    }

}
