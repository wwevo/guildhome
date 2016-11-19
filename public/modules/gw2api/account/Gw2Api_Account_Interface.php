<?php

interface Gw2Api_Account_Interface {
    public function setAccountId($account_id);
    public function setAccountName($account_name);
    public function setUserId($userid);
    public function setCreationDate($creation_date);
    public function setWorld($world);
    public function setCommander($commander);

    public function getAccountId();
    public function getAccountName();
    public function getUserId();
    public function getCreationDate();
    public function getWorld();
    public function getCommander();
}