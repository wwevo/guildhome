<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of activity
 *
 * @author ecv
 */
class Activity {
    
    function initEnv() {
        Toro::addRoute(["/activities" => "Activity"]);
    }
        
    function activity_menu() {
        $login = new Login();
        $page = Page::getInstance();
        $page->addContent('{##main##}', '<nav>');
        $page->addContent('{##main##}', '<ul>');
        $page->addContent('{##main##}', '<li><a href="/activities">10 days of EoL</a></li>');
        $page->addContent('{##main##}', '<li><a href="/activities/shouts">Shouts</a></li>');
        if ($login->isLoggedIn()) {
            $page->addContent('{##main##}', '<li class="add"><a href="/activity/shout/new">(+)</a></li>');
        }
        $page->addContent('{##main##}', '<li><a href="/activities/events">Events</a></li>');
        if ($login->isLoggedIn()) {
            $page->addContent('{##main##}', '<li class="add"><a href="/activity/event/new">(+)</a></li>');
        }
//        $page->addContent('{##main##}', '<li><a href="/activities/polls">Polls</a></li>');
//        if ($login->isLoggedIn()) {
//            $page->addContent('{##main##}', '<li class="add"><a href="/activity/poll/new">(+)</a></li>');
//        }
        $page->addContent('{##main##}', '</ul>');
        $page->addContent('{##main##}', '</nav>');
    }
    
    function get() {
        $env = Env::getInstance();
        $env->clearPost('activity');

        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Activities</h2>');
        $this->activity_menu();
        $page->addContent('{##main##}', $this->getAllActivitiesView());
//        $page->addContent('{##sidebar##}', '<aside>Change Identity</aside>');

    }
    
    function save($type = '1') {
        $db = db::getInstance();
        $login = new Login();
        
        $userid = $login->currentUserID();
        $uxtime = time();
        
        $sql = "INSERT INTO activities (id, userid, create_time, type) VALUES ('NULL', '$userid', '$uxtime', '$type');";
        $query = $db->query($sql);
    }

    function getActivityById($id = NULL) {
        if ($id === NULL) {
            return false;
        }
        $db = db::getInstance();
        $sql = "SELECT activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.name AS type_name, activity_types.description AS type_description
                    FROM activities
                    INNER JOIN activity_types
                    ON activities.type = activity_types.id
                    WHERE activities.id = $id
                    ORDER BY activities.create_time DESC LIMIT 1;";
        $query = $db->query($sql);
        
        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result;
        }
        return false;
    }
    
    function getActivities($interval = NULL) {
        $db = db::getInstance();
        
        $interval = (is_numeric($interval)) ? " LIMIT " . $interval : '';
        
        $sql = "SELECT activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.name AS type_name, activity_types.description AS type_description,
                    (SELECT concat(ae.date,' ', ae.time) as timestamp FROM activity_events ae WHERE ae.activity_id = activities.id HAVING DATE_ADD(timestamp,INTERVAL 2 HOUR) >= NOW() AND timestamp <= DATE_ADD(NOW(),INTERVAL 48 HOUR)) as event_date
                    FROM activities
                    INNER JOIN activity_types
                    ON activities.type = activity_types.id
                    ORDER BY event_date IS NULL, event_date ASC, activities.create_time DESC
                    $interval;
                ";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activities[] = $result_row;
            }
            return $activities;
        }
        return false;
    }
    
    /*
     * Spaghetti-Code at it's best :)
     */
    function getSubView($act_id = NULL, $compact = NULL) {
        $env = Env::getInstance();
        $act = $this->getActivityById($act_id);
        
        $type_name = (isset($act->type_name)) ? $act->type_name : NULL;

        if (isset($env::$hooks[$type_name])) {
            $event_data = $env::$hooks[$type_name]($act_id, $compact);
            return $event_data;
        }
        return false;
    }

    /*
     * Being a patchwork funtion at the moment, a lot of stuff is Jerry-Rigged here
     * A lot of stuff has to be worked out to be viable for public release
     * Whats clearly missing:
     *      - automatic detection of activity type and corresponding class
     */
    function getAllActivitiesView($type = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/list_all_activities.php'));
        $activities = ($type === NULL) ? $this->getActivities(10) : $this->getActivities();

        if (false !== $activities) {
            $activity_loop = NULL;
            foreach ($activities as $act) {
                if ($type !== NULL AND $act->type !== $type) {
                    continue;
                }
                
                $subView = $this->getSubView($act->id, $compact = TRUE);
                
                $activity_loop .= $subView;
            }
            $view->addContent('{##activity_loop##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }

    function getActivityView($id = NULL) {
        $subView = $this->getSubView($id, $view);
        return $subView;
    }
    
    function createSlug($str) {
	if ($str !== mb_convert_encoding(mb_convert_encoding($str, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32')) {
            $str = mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
        }
        $str = htmlentities($str, ENT_NOQUOTES, 'UTF-8');
        $str = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\\1', $str);
        $str = html_entity_decode($str, ENT_NOQUOTES, 'UTF-8');
        $str = preg_replace(array('`[^a-z0-9]`i','`[-]+`'), '-', $str);
        $str = strtolower(trim($str, '-'));
        return $str;
    }
   
}
$activity = new Activity();
$activity->initEnv();
