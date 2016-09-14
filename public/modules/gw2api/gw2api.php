<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of gw2api
 *
 * Doing some quick hacks to see whats out there. This will take the hell of a
 * lot of time to make this remotely modular :)
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
        
        $login = new Login();
        $settings = new Settings();

        $page->setContent('{##main##}', '<h2>Guild Wars 2 API Access</h2>');
        $api_key = $settings->getSettingByKey('api_key');
        if ($login->isLoggedIn()) {
            $page->addContent('{##main##}', $settings->getUpdateSettingForm('api_key'));
            if ($api_key !== false) {
                $page->addContent('{##main##}', $this->getApiKeyScopeView());
                $page->addContent('{##main##}', $this->getImportForm());
                $page->addContent('{##main##}', $this->getImportAccountnameForm());
                $page->addContent('{##main##}', $this->getAccountCharactersView());
            } else {
                $page->addContent('{##main##}', 'No Api key found');
            }
        } else {
            header("Location: /activities");
        }
        if ($login->isOperator()) {
            if ($api_key !== false) {
                $permissions = $this->getApiKeyScope();
                if (is_array($permissions) AND in_array('guilds', $permissions)) {
                    $page->addContent('{##main##}', $this->getUpdateRosterForm());
                }
            } else {
                $page->addContent('{##main##}', 'No Api key found');
            }

        }
    }
    
    function post($slug = '') {
        $login = new Login();
        $env = Env::getInstance();

        if ($login->isLoggedIn()) {
            if (isset($env->post('gw2api_import_accountname')['submit'])) {
                if ($slug == 'import_accountname') {
                    $this->importAccountname();
                    header("Location: /gw2api");
                }
            }
            if (isset($env->post('gw2api_import')['submit'])) {
                if ($slug == 'import') {
                    $this->storeApiData($this->fetchApiData());
                    header("Location: /gw2api");
                }
            }
        } else {
            header("Location: /activities");
        }
        if ($login->isOperator()) {
            if (isset($env->post('gw2api_update_roster')['submit'])) {
                if ($slug == 'update_roster') {
                    $this->extractRosterFromDump();
                    header("Location: /gw2api");
                }
            }
        }
    }
    
    function fetchApiData() {
        $settings = new Settings();
        $gw2apikey = $settings->getSettingByKey('api_key');
        
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $gw2apikey);
        $api_permissions = $api_tokeninfo['permissions'];

        if (is_array($api_permissions) === false) {
            return false;
        }
        $api_data['permissions'] = $api_permissions;
        foreach ($api_permissions as $permission) {
            ${'api_' . $permission} =  $this->gw2apiRequest('/v2/' . $permission, $gw2apikey);
        }
        if (isset($api_characters) AND is_array($api_characters)) {
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
            $api_data['characters'] = $characters;
        }

        if (isset($api_account) AND is_array($api_account)) {
            $api_data['account'] = $api_account;
            if (is_array($api_account['guilds'])) {
                foreach ($api_account['guilds'] as $key => $guild) {
                    $api_data['guilds'][$key] = $this->gw2apiRequest('/v2/guild/' . $guild);
                    $login = new Login();
                    if ($login->isLoggedIn() AND $login->isAdmin()) {
                        $api_data['guilds'][$key]['roster'] = $this->gw2apiRequest('/v2/guild/' . $guild . '/members', $gw2apikey);
                    }
                }
            }
        }

        if (is_array($api_data)) {
            $api_data['created'] = $date = date('m/d/Y h:i:s a');
            return $api_data;
        }
        return false;
    }
    
    function storeApiData($api_data) {
        $settings = new Settings();
        if ($settings->updateSetting('gw2apidata', json_encode($api_data)) == true) {
            return true;
        }
        return false;
    }
    
    function getImportForm() {
        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/import_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/import');
        $view->addContent('{##gw2api_import_submit_text##}', 'Import from API');
        $settings = new Settings();
        if (($current_accountname = $settings->getSettingByKey('gw2apidata')) !== false) {
            $view->addContent('{##import_status##}', 'You have local Data. An import will update everything!');
        } else {
            $view->addContent('{##import_status##}', 'Nothing fetched yet');
        }

        $view->replaceTags();
        return $view;
    }

    function getUpdateRosterForm() {
        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/update_roster_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/update_roster');
        $view->addContent('{##gw2api_update_roster_submit_text##}', 'Fetch roster');
        $view->replaceTags();
        return $view;
    }

    function importAccountname() {
        $settings = new Settings();
        if (($api_data = $settings->getSettingByKey('gw2apidata')) === false) {
            return false;
        }
        $api_data = json_decode($api_data, true);

        if (is_array($api_data['permissions']) === false OR !in_array('account', $api_data['permissions'])) {
            return false;
        }
        if ($settings->updateSetting('gw2_account', $api_data['account']['name']) === false) {
            return false;
        }

        return true;
    }
    
    function getImportAccountnameForm() {
        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/import_accountname_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/import_accountname');
        $view->addContent('{##gw2api_import_accountname_submit_text##}', 'Fetch accountname');

        $settings = new Settings();
        if (($current_accountname = $settings->getSettingByKey('gw2_account')) !== false) {
            $view->addContent('{##current_accountname##}', $current_accountname);
        } else {
            $view->addContent('{##current_accountname##}', 'Nothing fetched yet');
        }

        $view->replaceTags();
        return $view;
    }

    function getImportedDataDumpView() {
        $settings = new Settings();

        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/show_imported_data.php'));
        $view->addContent('{##data##}', "<pre>" . print_r(json_decode($settings->getSettingByKey('gw2apidata'), true), true) . "</pre>");
        $view->replaceTags();
        return $view;
    }
    
    function getApiKeyScope() {
        $settings = new Settings();
        $api_key = $settings->getSettingByKey('api_key');
        
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $api_key);
        $api_permissions = $api_tokeninfo['permissions'];

        if (is_array($api_permissions) === false) {
            return false;
        }

        return $api_permissions;
    }
    
    function getApiKeyScopeView() {
        $scope = $this->getApiKeyScope();
        if (FALSE !== $scope) {
            $view = new View();
            $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/api_key_scope_view.php'));
            $all_permissions = null;
            if (is_array($scope)) {
                foreach ($scope as $permission) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##permissions##}'));
                    $option->addContent('{##permission##}', $permission);
                    $option->replaceTags();
                    $all_permissions .= $option;
                }
            }
            if (is_null($all_permissions)) {
                $view->addContent('{##permissions##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##permissions##}', $all_permissions);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }
    
    
    function extractRosterFromDump() {
        $settings = new Settings();
        $roster = json_decode($settings->getSettingByKey('gw2apidata'), true)['guilds'][0]['roster'];
        foreach ($roster as $member) {
            $account = $member['name'];
            $rank =  $member['rank'];
            $values[] = "('$account', '$rank')";
        }
        $values = implode(',', $values);
        
        $db = db::getInstance();

        $sql = "TRUNCATE TABLE api_roster;";
        $query = $db->query($sql);
        $sql = "INSERT INTO api_roster (account_name, guild_rank) VALUES $values;";

        $query = $db->query($sql);
        if ($query !== false) {
            return true;
        }
        return false;
    }
    
    function getRankUsageFromRoster() {
        $db = db::getInstance();

        $sql = "SELECT guild_rank, COUNT(*) as rank_count FROM api_roster GROUP BY guild_rank;";

        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $rank_usage[] = $result_row;
            }
            return $rank_usage;
        }
        return false;
    }

    function getRankUsageFromRosterView() {
        $ranks = $this->getRankUsageFromRoster();
        if (FALSE !== $ranks) {
            $view = new View();
            $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/rank_usage_view.php'));
            $all_ranks = null;
            if (is_array($ranks)) {
                foreach ($ranks as $rank) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##ranks##}'));
                    $option->addContent('{##rank_description##}', $rank->guild_rank);
                    $option->addContent('{##rank_count##}', $rank->rank_count);
                    $option->replaceTags();
                    $all_ranks .= $option;
                }
            }
            if (is_null($all_ranks)) {
                $view->addContent('{##ranks##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##ranks##}', $all_ranks);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }
    
    function getAccountCharacters() {
        $settings = new Settings();
        $characters = json_decode($settings->getSettingByKey('gw2apidata'), true);

        if (isset($characters['characters']) AND is_array($characters['characters'])) {
            foreach ($characters['characters'] as $character) {
                $name = $character['name'];
                $race =  $character['race'];
                $gender =  $character['gender'];
                $profession =  $character['profession'];
                $level =  $character['level'];
                $guild =  $character['guild'];
                $age =  $character['age'];
                $created =  $character['created'];
                $deaths =  $character['deaths'];

                $characters_modified[] = array(
                    'name' => $name,
                    'race' => $race,
                    'gender' => $gender,
                    'profession' => $profession,
                    'level' => $level,
                    'guild' => $guild,
                    'age' => $age,
                    'created' => $created,
                    'deaths' => $deaths,
                );

            }
            return $characters_modified;
        } else {
            return false;
        }

    }

    function getAccountCharactersView() {
        $characters = $this->getAccountCharacters();
        if ($characters !== false) {
            $view = new View();
            $view->setTmpl(file('themes/' . constant('theme') . '/views/gw2api/account_characters_view.php'));
            $all_characters = null;
            if (is_array($characters)) {
                foreach ($characters as $character) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##characters##}'));
                    $option->addContent('{##level##}', $character['level']);
                    $option->addContent('{##name##}', $character['name']);
                    $option->addContent('{##race##}', $character['race']);
                    $option->addContent('{##gender##}', $character['gender']);
                    $option->addContent('{##guild##}', $character['guild']);
                    $option->addContent('{##age##}', $character['age']);
                    $now = new DateTime();
                    $birthday = new DateTime(date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - $character['age'], date("Y"))));
                    $next_birthday = $birthday;
                    $next_birthday->setDate(date("Y"), $birthday->format("m"), $birthday->format("d"));
                    if ($next_birthday < $now) {
                        $next_birthday->setDate(date("Y") +1, $birthday->format("m"), $birthday->format("d"));
                    }

                    $days_to_next_birthday = $next_birthday->diff($now);
                    
                    $option->addContent('{##birthday_in##}', $days_to_next_birthday->format('%D'));
                    $option->addContent('{##created##}', $character['created']);
                    $option->addContent('{##deaths##}', $character['deaths']);
                    $option->replaceTags();
                    $all_characters .= $option;
                }
            }
            if (is_null($all_characters)) {
                $view->addContent('{##characters##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##characters##}', $all_characters);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
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