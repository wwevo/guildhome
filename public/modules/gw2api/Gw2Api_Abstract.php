<?php

abstract class Gw2Api_Abstract implements IDatabaseModel, Gw2Api_Interface {

    protected abstract function createDatabaseTablesByType($overwriteIfExists);
    public function createDatabaseTables($overwriteIfExists) {
        return $this->createDatabaseTablesByType($overwriteIfExists);
    }

    static function gw2apiRequest($request, $api_key = "") {
        $log = Logger::getInstance();
        if ($api_key != "") {
            $pattern = "/^[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{20}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}$/";
            if (!preg_match($pattern, $api_key)) {
                return false;
            }
        }
        $url = parse_url('https://api.guildwars2.com' . $request);
        if (!$fp = @fsockopen('ssl://' . $url['host'], 443, $errno, $errstr, 5)) {
            $log::lwrite("$errstr ($errno)");
            return false;
        }
        $nl = "\r\n";
        $query = (isset($url['query']) ? '?' . $url['query'] : '');
        $header = 'GET ' . $url['path'] . $query . ' HTTP/1.1' . $nl;
        $header .= 'Host: ' . $url['host'] . $nl;
        $header .= !empty($api_key) ? 'Authorization: Bearer ' . $api_key . $nl : '';
        $header .= 'Connection: Close' . $nl . $nl;
        fwrite($fp, $header);
        stream_set_timeout($fp, 5);
        $response = '';
        $eof = false;
        do {
            $in = fread($fp, 1024 * 8);
            if (strlen($in) == 0) {
                $eof = true;
            } else {
                $response .= $in;
            }
        } while ($eof === false);
        //        $log::lwrite($response . $nl);
        $response_lines = explode($nl, $response);
        if (isset($response_lines[0]) && $response_lines[0] == 'HTTP/1.1 200 OK') {
            // gw2 api sends their actual api-data in the last line
            $api_response = json_decode($response_lines[count($response_lines) - 1], true);
            return $api_response;
        }
        return false;
    }

    public function attemptSave() {
        if (false !== $this->isValid()) {
            return $this->save();
        }
        return false;
    }

    abstract protected function isValid();

    abstract public function save();

}
