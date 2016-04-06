<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Menu
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Menu {

    private static $instance;
    
    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
            self::getEnv();
        }
        return static::$instance;
    }
    
    private static function getEnv() {
    }
        
}
