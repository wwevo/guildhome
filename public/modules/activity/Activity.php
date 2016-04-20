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
    
    function create_tables() {
        // Dirty Setup
        $db = db::getInstance();
        $sql = "CREATE TABLE activities (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            userid INT(6) NOT NULL,
            create_time INT(11) NOT NULL,
            type INT(6)
        )";
        $result = $db->query($sql);
        echo $sql;

        $sql = "CREATE TABLE activity_types (
            id INT(6) UNSIGNED PRIMARY KEY,
            name VARCHAR(32),
            description VARCHAR(50)
        )";
        $result = $db->query($sql);
        echo $sql;
    }
    
    function activity_menu() {
        $login = new Login();
        $page = Page::getInstance();
        $page->addContent('{##main##}', '<a href="/activities">10 days of EoL</a> ');
        $page->addContent('{##main##}', '<a href="/activities/shouts">Shouts</a> ');
        if ($login->isLoggedIn()) {
            $page->addContent('{##main##}', '<a href="/activity/shout/new">(+)</a> ');
        }
        $page->addContent('{##main##}', '<a href="/activities/events">Events</a> ');
        if ($login->isLoggedIn()) {
            $page->addContent('{##main##}', '<a href="/activity/event/new">(+)</a>');
        }
    }
    
    function get() {
        $env = Env::getInstance();
        $env->clear_post('activity');

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
        $sql = "SELECT activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.description AS type_description
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
        
        $interval = (is_numeric($interval) AND $interval <= 10) ? $interval : NULL;
        $interval = !is_null($interval) ? "HAVING create_time >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)" : '';
        
        $sql = "SELECT activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.description AS type_description
                    FROM activities
                    INNER JOIN activity_types
                    ON activities.type = activity_types.id
                    $interval
                    ORDER BY activities.create_time DESC;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activities[] = $result_row;
            }
            return $activities;
        }
        return false;
    }
    
    function getSubView($act = NULL, $view = NULL) {
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
        $subView->addContent('{##activity_published##}', $act->create_time);
        $subView->addContent('{##activity_type##}',  $act->type_description);
        switch ($act->type) {
            case '1' : 
                $shout = new Activity_Shout();
                $activity_shout = $shout->getActivity($act->id);
                $content = Parsedown::instance()->text($activity_shout->content);
                if (isset($activity_shout->comments_activated) AND $activity_shout->comments_activated == '1') {
                    $allow_comments = TRUE;
                } else {
                    $allow_comments = FALSE;
                }
                $delete_link = '/activity/shout/delete/' . $act->id;
                $update_link = '/activity/shout/update/' . $act->id;
                $comment_link = '/comment/activity/view/' . $act->id;
                $details_link = '';
                break;
            case '2' : 
                $event = new Activity_Event();
                $activity_event = $event->getActivity($act->id);
                if (isset($activity_event->comments_activated) AND $activity_event->comments_activated == '1') {
                    $allow_comments = TRUE;
                } else {
                    $allow_comments = FALSE;
                }
                
                $event_data =  Parsedown::instance()->text($activity_event->title);
                // $event_data .= Parsedown::instance()->text($activity_event->description);
                $event_data .= $activity_event->date . " @ ";
                $event_data .= $activity_event->time;
                    
                $content = $event_data;
                $delete_link = '/activity/event/delete/' . $act->id;
                $update_link = '/activity/event/update/' . $act->id;
                $comment_link = '/comment/activity/view/' . $act->id;
                $details_link = '/activity/event/details/' . $act->id;
                break;
        }
        $subView->addContent('{##activity_content##}',  $content);

        $identity = new Identity();
        $subView->addContent('{##activity_identity##}', $identity->getIdentityById($act->userid, 0));
        $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($act->userid));

        if ($allow_comments === TRUE) {
            $comment = new Comment();
            $comment_count = $comment->getCommentCount($act->id);
            $subView->addContent('{##comment_link##}', $comment_link);
            $subView->addContent('{##comment_link_text##}',  'comments (' . $comment_count . ')');
        }
        
        $login = new Login();
        if ($login->isLoggedIn()) {
            $memberView = new View();
            $memberView->setTmpl($view->getSubTemplate('{##activity_logged_in##}'));
            if ($login->currentUserID() === $act->userid) {
                $memberView->addContent('{##delete_link##}', $delete_link);
                $memberView->addContent('{##delete_link_text##}',  'delete');
                $memberView->addContent('{##update_link##}', $update_link);
                $memberView->addContent('{##update_link_text##}',  'update');
            }
            $memberView->replaceTags();
            $subView->addContent('{##activity_logged_in##}',  $memberView);
        }

        $detailsView = new View();
        $detailsView->setTmpl($view->getSubTemplate('{##activity_details##}'));
        $detailsView->addContent('{##details_link##}', $details_link);
        $detailsView->addContent('{##details_link_text##}',  'details');
        $detailsView->replaceTags();

        $subView->addContent('{##activity_details##}',  $detailsView);
        $subView->replaceTags();
        return $subView;
    }

    /*
     * Being a patchwork funtion at the moment, a lot of stuff is Jerry-Rigged here
     * A lot of stuff has to be worked out to be viable for public release
     * Whats clearly missing:
     *      - automatic detection of activity type and corresponding class
     */
    function getAllActivitiesView($type = NULL) {
        $view = new View();
        $view->setTmpl(file('views/activity/list_all_activities.php'));
        $activities = ($type === NULL) ? $this->getActivities(10) : $this->getActivities();

        if (false !== $activities) {
            $activity_loop = NULL;
            foreach ($activities as $act) {
                if ($type !== NULL AND $act->type !== $type) {
                    continue;
                }
                $subView = $this->getSubView($act, $view);
                $activity_loop .= $subView;
            }
            $view->addContent('{##activity_loop##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }

    function getActivityView($id = NULL) {
        $view = new View();
        $view->setTmpl(file('views/activity/list_all_activities.php'));
        $act = $this->getActivityById($id);
        if (false !== $act) {
            $subView = $this->getSubView($act, $view);
            $view->addContent('{##activity_loop##}',  $subView);
        }
        $view->replaceTags();
        return $view;
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
