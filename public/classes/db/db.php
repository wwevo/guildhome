<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DB
 *
 * @author ecv
 */
class db {

    private static $dbInstance = NULL;
    
    public static $host;
    public static $user;
    public static $pass;
    public static $dbname;

    public function __construct() {}
    public function __clone() {}

    public static function getInstance() {
        if (self::$dbInstance === NULL) {
            self::$dbInstance = new self;
            self::connect();
        }
        
        return self::$dbInstance;
    }

    private static function connect() {
        self::$dbInstance = new mysqli(self::$host, self::$user, self::$pass, self::$dbname);
        self::$dbInstance->set_charset("utf8");
    }

}

include_once 'config/db.php';
