<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of gw2api
 *
 * @author ecv
 */
class gw2api {
    
    function initEnv() {
        Toro::addRoute(["/gw2api" => "gw2api"]);
        Toro::addRoute(["/gw2api/:alpha" => "gw2api"]);
    }
    
    function get($slug = '') {
        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Guild Wars 2 API Access</h2>');
        
        $login = new Login();
        if ($login->isLoggedIn()) {
            $settings = new Settings();
            if ($slug == 'dump') {
                $page->addContent('{##main##}', $this->dumpAllDataView());
            } else {
                $page->addContent('{##main##}', $settings->getUpdateSettingForm('api_key'));
                $page->addContent('{##main##}', $this->getImportForm());
                $page->addContent('{##main##}', $this->getImportedData());
                // $page->addContent('{##main##}', '<a href="/gw2api/dump">Data-dump (test)</a> : this may take a few seconds to process!');
            }
        } else {
            header("Location: /activities");
        }
    }
    
    function post($slug = '') {
        $env = Env::getInstance();

        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Guild Wars 2 API Access</h2>');
        
        if (isset($env->post('gw2api_import')['submit'])) {
            if ($slug == 'import') {
                $page->addContent('{##main##}', $this->saveImportedData());
                header("Location: /gw2api");
            }
        }
    }
    
    function saveImportedData() {
        $settings = new Settings();
        $gw2apikey = $settings->getSettingByKey('gw2apikey');
        
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $gw2apikey);
        $api_permissions = $api_tokeninfo['permissions'];
        
        if (is_array($api_permissions) === false) {
            return false;
        }
        
        foreach ($api_permissions as $permission) {
            ${'api_' . $permission} =  $this->gw2apiRequest('/v2/' . $permission, $gw2apikey);
        }

        if (is_array($api_characters)) {
            foreach ($api_characters as $key => $value) {
                $characters[$key] = $this->gw2apiRequest('/v2/characters/' . rawurlencode($value), $gw2apikey);
                if (!empty($characters[$key]['guild'])) {
                    $characters[$key]['guild'] = $this->gw2apiRequest('/v1/guild_details.json?guild_id=' . $characters[$key]['guild'])['guild_name'];
                }
                $oDateNow = new DateTime();
                $oDateBirth = new DateTime($characters[$key]['created']);
                $oDateIntervall = $oDateNow->diff($oDateBirth, true);
                $characters[$key]['age'] = $oDateIntervall->format('%R%a');
            }
        }

        if ($settings->updateSetting('gw2apidata', json_encode($characters)) == true) {
            return true;
        }
        return false;
    }
    
    function getImportForm() {
        $view = new View();
        $view->setTmpl(file('views/gw2api/import_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/import');
        $view->addContent('{##gw2api_import_submit_text##}', 'Import!');
        $view->replaceTags();
        return $view;
    }

    function getImportedData() {
        $settings = new Settings();

        $view = new View();
        $view->setTmpl(file('views/gw2api/show_imported_data.php'));
        $view->addContent('{##data##}', "<pre>" . print_r(json_decode($settings->getSettingByKey('gw2apidata'), true), true) . "</pre>");
        $view->replaceTags();
        return $view;
    }
    
    function dumpAllDataView() {
        $view = new View();
        $view->setTmpl(file('views/gw2api/dump_all_data_view.php'));
        $view->addContent('{##data##}', '<pre>');
        $view->addContent('{##data##}', print_r($api_tokeninfo, true));
        $view->addContent('{##data##}', print_r($api_account, true));
        $view->addContent('{##data##}', print_r($characters, true));
        $view->addContent('{##data##}', '</pre>');
        $view->replaceTags();
        return $view;
    }

    function gw2apiRequest($request, $api_key = ""){
            // Check API Key against pattern
            if($api_key != ""){
                $pattern="/^[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{20}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}$/";
                if(!preg_match($pattern, $api_key)) {
                    return false;
                }
            }

            $url = parse_url('https://api.guildwars2.com'.$request);
            // open the socket
            if(!$fp = @fsockopen('ssl://'.$url['host'], 443, $errno, $errstr, 5)) {
                return false;
            }
            // prepare the request header...
            $nl = "\r\n";
            $header = 'GET '.$url['path'].(isset($url['query']) ? '?'.$url['query'] : '').' HTTP/1.1'.$nl.'Host: '.$url['host'].$nl;
            $header .= !empty($api_key) ? 'Authorization: Bearer '.$api_key.$nl : '';
            $header .= 'Connection: Close'.$nl.$nl;

            // ...and send it.
            fwrite($fp, $header);
            stream_set_timeout($fp, 5);
            // receive the response
            $response = '';
            do {
                if(strlen($in = fread($fp, 1024)) == 0){
                    break;
                }
                $response.= $in;
            } while(true);
            // now the nasty stuff... explode the response at the newlines
            $response = explode($nl, $response);
            // you may want some advanced error handling over here, too
            if(isset($response[0]) && $response[0] == 'HTTP/1.1 200 OK'){
                // the response is non chunked, so we can assume the data is contained in the last line
                $response = json_decode($response[count($response)-1], true);
                return $response;
            }
            return false;
    }
}
$gw2api = new gw2api();
$gw2api->initEnv();