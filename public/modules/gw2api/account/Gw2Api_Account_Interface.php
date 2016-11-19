<?php

interface Gw2Api_Account_Interface {
    public function setAccountName($account_name);
    public function setUserId($userid);
    public function setApiKeyId($api_key_id);
    public function getAccountName();
    public function getUserId();
    public function getApiKeyId();
}