<?php

interface Gw2Api_Keys_Interface {
    public function setId($id);
    public function setApiKey($api_key);
    public function setUserId($user_id);
    public function setApiKeyName($api_key_name);
    public function getId();
    public function getApiKey();
    public function getUserId();
    public function getApiKeyName();
}