<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Env
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Env {

    private static $instance;
    private $_post = [];
    private $_get = [];
    
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
        $inst = self::getInstance();

        $inst->_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (is_array($inst->_post) && !empty($inst->_post)) {
            $_SESSION['evo']['post_messages'] = $inst->_post;
        }
        
        $inst->_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        if (is_array($inst->_get) && !empty($inst->_get)) {
            $_SESSION['evo']['post_messages'] = $inst->_get;    
        }
        
    }
    
    public static function clear_get($key) {
        $inst = self::getInstance();
        unset($inst->_get[$key]);
        unset($_SESSION['evo']['get_messages'][$key]);
    }

    public static function clear_post($key) {
        $inst = self::getInstance();
        unset($inst->_post[$key]);
        unset($_SESSION['evo']['post_messages'][$key]);
    }
    
    public static function get($key) {
        $inst = self::getInstance();
        if (isset($_SESSION['evo']['get_messages'])) {
            $inst->_get = $_SESSION['evo']['get_messages'];
        }
        if (isset($inst->_get[$key])) {
            return $inst->_get[$key];
        } else {
            return false;
        }
    }

    public static function post($key) {
        $inst = self::getInstance();
        if (isset($_SESSION['evo']['post_messages'])) {
            $inst->_post = $_SESSION['evo']['post_messages'];
        }
        if (isset($inst->_post[$key])) {
            return $inst->_post[$key];
        } else {
            return false;
        }
    }
    
}
