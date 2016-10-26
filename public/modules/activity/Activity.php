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
        
    function activityMenu() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));

        $login = new Login();
        $view->addContent('{##data##}', '<nav class="activities">');
        $view->addContent('{##data##}', '<ul>');
        $view->addContent('{##data##}', '<li><a href="/activities">10 days of EoL</a></li>');

        $view->addContent('{##data##}', '<li>');
        $view->addContent('{##data##}', '<a href="/activities/shouts">Shouts</a>');
        if ($login->isLoggedIn()) {
            $view->addContent('{##data##}', ' <a href="/activity/shout/new">(+)</a>');
        }
        $view->addContent('{##data##}', '</li>');
        
        $view->addContent('{##data##}', '<li>');
        $view->addContent('{##data##}', '<a href="/activities/events">Events</a>');
        if ($login->isLoggedIn()) {
            $view->addContent('{##data##}', ' <a href="/activity/event/new">(+)</a>');
        }
        $view->addContent('{##data##}', '</li>');
//        $view->addContent('{##data##}', '<li><a href="/activities/polls">Polls</a></li>');
//        if ($login->isLoggedIn()) {
//            $view->addContent('{##data##}', '<li class="add"><a href="/activity/poll/new">(+)</a></li>');
//        }
        $view->addContent('{##data##}', '</ul>');
        $view->addContent('{##data##}', '</nav>');
        $view->replaceTags();
        return $view;
    }
    
    function get() {
        $env = Env::getInstance();
        $env->clearPost('activity');

        $page = Page::getInstance();
        $page->setContent('{##main##}', $this->activityMenu());
        $page->addContent('{##main##}', '<h2>Activities of the last 10 Days</h2>');
        $page->addContent('{##main##}', $this->getAllActivitiesView());
    }
    
    function getActivityById($id = NULL) {
        if ($id === NULL) {
            return false;
        }
        $db = db::getInstance();
        $sql = "SELECT activities.deleted as deleted, activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.name AS type_name, activity_types.description AS type_description
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
        
        $interval_sql = (is_numeric($interval)) ? "HAVING create_time >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)" : '';
        
        $sql = "SELECT activities.id, activities.userid, activities.create_time AS timestamp, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.name AS type_name, activity_types.description AS type_description,
                    (SELECT concat(ae.date, ' ', ae.time) AS timestamp
                        FROM activity_events ae
                        WHERE ae.activity_id = activities.id
                        HAVING DATE_ADD(timestamp,INTERVAL 2 HOUR) >= NOW() AND timestamp <= DATE_ADD(NOW(),INTERVAL 48 HOUR)
                    ) AS event_date,
                    DAY(from_unixtime(activities.create_time)) as event_day
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE activities.deleted = 0
                    $interval_sql
                    ORDER BY event_date IS NULL, event_date ASC, activities.create_time DESC";
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
            $d_var = getdate($activities[0]->timestamp);
            $activity_loop = '';
            if (!isset($activities[0]->event_date)) {
                $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time>' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
            }
            $activity_loop .= '<ul class="day_wrapper">';
            $activity_loop .= '<li><ul class="activity_wrapper ' . $activities[0]->type_name . '">';
            $last_type = $activities[0]->type;
            $last_day = $activities[0]->event_day;
            foreach ($activities as $act) {
                if ($type !== NULL AND $act->type !== $type) {
                    continue;
                }
                
                if ($last_day != $act->event_day) {
                    $activity_loop .= '</ul></li></ul>';
                    $d_var = getdate($act->timestamp);
                    if (!isset($act->event_date)) {
                        $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time>' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
                    }
                    $activity_loop .= '<ul class="day_wrapper">';
                    $activity_loop .= '<li><ul class="activity_wrapper ' . $act->type_name . ' ' . $act->id . '">';
                    $last_day = $act->event_day;
                }

                if ($last_type != $act->type) {
                    $activity_loop .= '</ul>';
                    $activity_loop .= '<ul class="activity_wrapper ' . $act->type_name . '">';
                    $last_type = $act->type;
                }

                $subView = $this->getActivityView($act->id, $compact = TRUE);
                $activity_loop .= '<li>' . $subView . '</li>';

            }
            $activity_loop .= '</ul></li>';
            $activity_loop .= '</ul>';
            $view->addContent('{##activity_loop##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
    /*
     * Spaghetti-Code at it's best :)
     */
    function getActivityView($act_id = NULL, $compact = NULL) {
        $env = Env::getInstance();
        $act = $this->getActivityById($act_id);
        
        $type_name = (isset($act->type_name)) ? $act->type_name : NULL;

        if (isset($env::$hooks[$type_name])) {
            $event_data = $env::$hooks[$type_name]($act_id, $compact);
            return $event_data;
        }
        return false;
    }

    function save($type = '1') {
        $db = db::getInstance();
        $login = new Login();
        
        $userid = $login->currentUserID();
        $uxtime = time();
        
        $sql = "INSERT INTO activities (id, userid, create_time, type) VALUES ('NULL', '$userid', '$uxtime', '$type');";
        $db->query($sql);
    }

}
$activity = new Activity();
$activity->initEnv();
