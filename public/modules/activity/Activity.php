<?php
class Activity {
    // start controller (i guess)
    function initEnv() {
        Toro::addRoute(["/activities" => "Activity"]);
    }
    
    function get() {
        $env = Env::getInstance();
        $env->clearPost('activity');

        $page = Page::getInstance();
        $page->setContent('{##main##}', $this->activityMenu('activity'));
        $page->addContent('{##main##}', $this->getAllActivitiesView());
    }
    // end controller    
    // start model (i suppose)
    function getActivities($interval = NULL, $type = NULL) {
        $db = db::getInstance();
        
        $interval_sql = (is_numeric($interval)) ? "HAVING create_time >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)" : "";
        $type_sql = (is_numeric($type)) ? " AND activities.type = $type" : "";
        
        $sql = "SELECT activities.deleted as deleted, activities.id, activities.userid, activities.create_time AS timestamp, activities.type AS type, activity_types.name AS type_name, activity_types.description AS type_description,
                    from_unixtime(activities.create_time) AS create_time,
                    (SELECT concat(ae.date, ' ', ae.time) AS timestamp
                        FROM activity_events ae
                        WHERE ae.activity_id = activities.id
                        HAVING DATE_ADD(timestamp,INTERVAL 2 HOUR) >= NOW() AND timestamp <= DATE_ADD(NOW(),INTERVAL 48 HOUR)
                    ) AS event_date,
                    DAY(from_unixtime(activities.create_time)) as event_day
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE activities.deleted = 0 $type_sql
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

    function getActivityById($id = NULL) {
        if ($id === NULL) {
            return false;
        }
        $db = db::getInstance();
        $sql = "SELECT activities.deleted as deleted, activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.name AS type_name, activity_types.description AS type_description, activities.comments_enabled AS comments_enabled
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
    
    function getActivityCountByType($type = 0) {
        $db = db::getInstance();
        $sql = "SELECT
                    (SELECT count(*) AS count FROM activities WHERE type = $type) AS count_all,
                    count(*) AS count
                    FROM activities
                    WHERE
                        deleted = 0 AND type = $type
                    AND
                        from_unixtime(create_time) >= DATE_SUB(CURDATE(), INTERVAL 10 DAY);";
        $query = $db->query($sql);
        
        if ($query !== false AND $query->num_rows == 1) {
            $count = $query->fetch_object();
            return $count;
        }
        return false;
    }
    
    function save($type = '1', $comments_enabled = '0') {
        $db = db::getInstance();
        $login = new Login();
        
        $userid = $login->currentUserID();
        $uxtime = time();
        
        $sql = "INSERT INTO activities (id, userid, create_time, type, comments_enabled) VALUES ('NULL', '$userid', '$uxtime', '$type', '$comments_enabled');";
        $query = $db->query($sql);
        
        if ($query !== false) {
            return $db->insert_id;
        }
        return false;
    }
    
    function commentsEnabled($activity_id) {
        $db = db::getInstance();
        $sql = "SELECT comments_enabled FROM activities WHERE id = '$activity_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $result_row = $query->fetch_object();
            $result = ($result_row->comments_enabled == '1') ? true : false;
            return $result;
        }
        return false;
    }
    // end model
    // start view (i'd say)
    /*
     * just a mockup, this should one day be converted to a real menu-handling
     * class thingy
     */
    function activityMenu($active = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));

        $login = new Login();
        $view->addContent('{##data##}', '<nav class="activities clearfix">');
        if ($active == NULL) {
            $view->addContent('{##data##}', '<div class="header active">');
        } else {
            $view->addContent('{##data##}', '<div class="header">');
        }
        $view->addContent('{##data##}', '<h2><a href="/activities">Activity Stream (last 10 days)</a></h2>');
        $view->addContent('{##data##}', '</div>');
        $view->addContent('{##data##}', '<ul>');

        if ($active == 'shout') {
            $view->addContent('{##data##}', '<li class="active">');
        } else {
            $view->addContent('{##data##}', '<li>');
        }
        $view->addContent('{##data##}', '<div class="count">');
        $count = $this->getActivityCountByType('1');
        $view->addContent('{##data##}', '<a href="/activities/shouts">' . $count->count . '</a>');
        $view->addContent('{##data##}', '</div>');
        $view->addContent('{##data##}', '<div class="title"><a href="/activities/shouts">Shouts</a> (' . $count->count_all . ')</div>');
        if ($login->isLoggedIn()) {
            $view->addContent('{##data##}', '<div class="action"><a href="/activity/shout/new">+</a></div>');
        }
        $view->addContent('{##data##}', '</li>');
        
        if ($active == 'event') {
            $view->addContent('{##data##}', '<li class="active">');
        } else {
            $view->addContent('{##data##}', '<li>');
        }
        $view->addContent('{##data##}', '<div class="count">');
        $count = $this->getActivityCountByType('2');
        $view->addContent('{##data##}', '<a href="/activities/events">' . $count->count . '</a>');
        $view->addContent('{##data##}', '</div>');
        $view->addContent('{##data##}', '<div class="title"><a href="/activities/events">Events</a> (' . $count->count_all . ')</div>');
        if ($login->isLoggedIn()) {
            $view->addContent('{##data##}', '<div class="action"><a href="/activity/event/new">+</a></div>');
        }
        $view->addContent('{##data##}', '</li>');

//        if ($active == 'poll') {
//            $view->addContent('{##data##}', '<li class="active">');
//        } else {
//            $view->addContent('{##data##}', '<li>');
//        }
//        $view->addContent('{##data##}', '<div class="count">');
//        $count = $this->getActivityCountByType('3');
//        $view->addContent('{##data##}', '<a href="/activities/polls">' . $count->count . '</a>');
//        $view->addContent('{##data##}', '</div>');
//        $view->addContent('{##data##}', '<div class="title"><a href="/activities/polls">Polls</a> (' . $count->count_all . ')</div>');
//        if ($login->isLoggedIn() AND $login->isOperator()) {
//            $view->addContent('{##data##}', '<div class="action"><a href="/activity/poll/new">+</a></div>');
//        }
//        $view->addContent('{##data##}', '</li>');
        $view->addContent('{##data##}', '</ul>');
        $view->addContent('{##data##}', '</nav>');
        $view->replaceTags();
        return $view;
    }

    /*
     * being a patchwork funtion at the moment, a lot of stuff is jerry-rigged here
     * to-do: make this template based, make it human readable again ^^
     */   
    function getAllActivitiesView($type = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        
        $activities = ($type === NULL) ? $this->getActivities(10) : $this->getActivities(NULL, $type);
        if (false !== $activities) {
            $d_var = getdate($activities[0]->timestamp);
            $activity_loop = '';
            if ($activities[0]->event_date == NULL) {
                $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time datetime="'.date('c', $activities[0]->timestamp).'">' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
            }
            $activity_loop .= '<ul class="day_wrapper">';
            $activity_loop .= '<li><ul class="activity_wrapper ' . $activities[0]->type_name . '">';
            $last_type = $activities[0]->type;
            $last_day = $activities[0]->event_day;
            foreach ($activities as $act) {
                if ($last_day != $act->event_day) {
                    $activity_loop .= '</ul></li></ul>';
                    $d_var = getdate($act->timestamp);
                    if ($act->event_date == NULL) {
                        $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time datetime="'.date('c', $act->timestamp).'">' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
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
            $view->addContent('{##data##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
    /*
     * this is my attempt of making this modular. every activity-submodule can
     * register it's output-view in the env-class and we will look for that here.
     * feel free to come up with something nicer
     */
    function getActivityView($act_id = NULL, $compact = NULL) {
        $env = Env::getInstance();
        $act = $this->getActivityById($act_id);
        
        $type_name = (isset($act->type_name)) ? $act->type_name : NULL;

        if (isset($env::$hooks[$type_name])) {
            $event_data = $env::$hooks[$type_name]($act_id, $compact);
            return $event_data;
        } else {
            // you can place a fallback here, at least the activity meta-data
            // should be available
        }
        return false;
    }
    // end view
}
$activity = new Activity();
$activity->initEnv();
