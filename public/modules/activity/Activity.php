<?php

class Activity extends Pagination {
    // start model (i suppose)
    function getActivityById($id = NULL) {
        if ($id === NULL) {
            return false;
        }
        $db = db::getInstance();
        $sql = "SELECT
                    activities.id,
                    activities.userid,
                    activities.create_time AS timestamp,
                    activities.comments_enabled AS comments_enabled,
                    activities.deleted as deleted,
                    activities.type AS type,
                    activity_types.name AS type_name,
                    activity_types.description AS type_description,
                    from_unixtime(activities.create_time) AS create_time,
                    (SELECT concat(ae.date, ' ', ae.time) AS timestamp
                        FROM activity_events ae
                        WHERE ae.activity_id = activities.id
                        HAVING DATE_ADD(timestamp,INTERVAL 2 HOUR) >= NOW() AND timestamp <= DATE_ADD(NOW(),INTERVAL 48 HOUR)
                    ) AS event_date,
                    DAY (from_unixtime(activities.create_time)) as event_day
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE activities.id = $id
                    LIMIT 1;";
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
                    (SELECT
                        count(*) AS count
                        FROM activities
                        WHERE
                            type = $type
                        AND
                            deleted = 0
                    ) AS count_all,
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
    function getActivityTypeIDByName($activity_type_name = null) {
        if ($activity_type_name === NULL) {
            return false;
        }
        $db = db::getInstance();
        $type_name = $db->real_escape_string(strip_tags($activity_type_name, ENT_QUOTES));

        $sql = "SELECT
                    activities.type AS type
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE
                        activity_types.name = '$type_name'
                    OR
                        activity_types.name_plural = '$type_name'
                    LIMIT 1;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result->type;
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
    /*
     * just a mockup, this should one day be converted to a real menu-handling
     * class thingy
     */
    function activityMenu($active = NULL, $compact = false) {
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
        if ($compact === true) {
            $view->addContent('{##data##}', '<ul class="activity_menu compact">');
        } else {
            $view->addContent('{##data##}', '<ul class="activity_menu">');
        }

        if ($active == 'shouts' OR $active == 'shout') {
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
        
        if ($active == 'events' OR $active == 'event') {
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

//        if ($active == 'polls' OR $active == 'poll') {
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
    // end view
}
