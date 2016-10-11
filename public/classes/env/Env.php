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
    private $post = [];
    private $get = [];

    public static $hooks = [];

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

    static function registerHook($key, callable $callback) {
        self::$hooks[$key] = $callback;
        return true;
    }
    
    private static function getEnv() {
        $inst = self::getInstance();

        $inst->post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if (is_array($inst->post) && !empty($inst->post)) {
            $_SESSION['evo']['post_messages'] = $inst->post;
        }
        
        $inst->get = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        if (is_array($inst->get) && !empty($inst->get)) {
            $_SESSION['evo']['get_messages'] = $inst->get;    
        }
    }

    public static function clearPost($key) {
        $inst = self::getInstance();
        unset($inst->post[$key]);
        unset($_SESSION['evo']['post_messages'][$key]);
    }
    
    public static function get($key) {
        $inst = self::getInstance();
        if (isset($_SESSION['evo']['get_messages'])) {
            $inst->get = $_SESSION['evo']['get_messages'];
        }
        if (isset($inst->get[$key])) {
            return $inst->get[$key];
        } else {
            return false;
        }
    }

    public static function post($key) {
        $inst = self::getInstance();
        if (isset($_SESSION['evo']['post_messages'])) {
            $inst->post = $_SESSION['evo']['post_messages'];
        }
        if (isset($inst->post[$key])) {
            return $inst->post[$key];
        } else {
            return false;
        }
    }
    
    function generateTimezoneList() {
        static $regions = array(
            DateTimeZone::AFRICA,
            DateTimeZone::AMERICA,
            DateTimeZone::ANTARCTICA,
            DateTimeZone::ASIA,
            DateTimeZone::ATLANTIC,
            DateTimeZone::AUSTRALIA,
            DateTimeZone::EUROPE,
            DateTimeZone::INDIAN,
            DateTimeZone::PACIFIC,
        );

        $timezones = array();
        foreach( $regions as $region ) {
            $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
        }

        $timezone_offsets = array();
        foreach( $timezones as $timezone ) {
            $tz = new DateTimeZone($timezone);
            $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
        }

        // sort timezone by timezone name
        asort($timezone_offsets);

        $timezone_list = array();
        foreach( $timezone_offsets as $timezone => $offset ) {
            $offset_prefix = $offset < 0 ? '-' : '+';
            $offset_formatted = gmdate( 'H:i', abs($offset) );

            $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

            $t = new DateTimeZone($timezone);
            $c = new DateTime(null, $t);
            $current_time = $c->format('g:i A');

            $timezone_list[$timezone] = "(${pretty_offset}) $timezone - $current_time";
        }

        return $timezone_list;
    }
    
}
