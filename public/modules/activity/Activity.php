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
        
        $interval = (is_numeric($interval)) ? " LIMIT " . $interval : '';
        
        $sql = "SELECT activities.id, activities.userid, from_unixtime(activities.create_time) AS create_time, activities.type AS type, activity_types.description AS type_description,
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
    function getSubView($act = NULL, $view = NULL, $compact = NULL) {
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
        if (isset($act->create_time)) {
            $subView->addContent('{##activity_published##}', $act->create_time);
        }
        if (isset($act->type_description)) {
            $subView->addContent('{##activity_type##}',  $act->type_description);
        }
        if (isset($act->event_date)) {
            $subView->addContent('{##css##}', ' pulled_to_top');
        }
        $type = (isset($act->type)) ? $act->type : NULL;

        switch ($type) {
            default: 
                $content = '';
                $allow_comments = FALSE;
                $delete_link = '';
                $update_link = '';
                $comment_link = '';
                $details_link = '';
                break;
            case '1' : 
                $shout = new Activity_Shout();
                $activity_event = $shout->getActivity($act->id);
                $content = Parsedown::instance()->text($activity_event->content);
                if (isset($activity_event->comments_activated) AND $activity_event->comments_activated == '1') {
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
                $delete_link = '/activity/event/delete/' . $act->id;
                $update_link = '/activity/event/update/' . $act->id;
                $comment_link = '/comment/activity/view/' . $act->id;
                $details_link = '/activity/event/details/' . $act->id;

                $event = new Activity_Event();
                $activity_event = $event->getActivity($act->id);
                
                if (isset($activity_event->comments_activated) AND $activity_event->comments_activated == '1') {
                    $allow_comments = TRUE;
                } else {
                    $allow_comments = FALSE;
                }
                
                $event_data = Parsedown::instance()->text($activity_event->description);
                if (!is_null($compact)) {
                    $link_view = new View();
                    $link_view->setTmpl($view->getSubTemplate('{##link_more##}'));
                    $link_view->addContent('{##link_more_link##}', $details_link);
                    $link_view->addContent('{##link_more_link_text##}', '...more');
                    $link_view->replaceTags();
                    $link_more = $link_view;
                    
                    $subView->addContent('{##link_more##}',  $link_more);

                    $event_data = substr($event_data, 0, 100);
                }
                $event_date = $activity_event->date . " @ ";
                $event_date .= $activity_event->time;
                $subView->addContent('{##activity_event_date##}', $event_date);
                    
                $content = $event_data;
                $signups = '';
                if ($activity_event->signups_activated) {
                    $signups = "Signed up:" . $event->getSignupCountByEventId($act->id);
                }
                
                if ($activity_event->maximal_signups_activated) {
                    $signups .= "/" . $activity_event->maximal_signups;
                }

                if ($activity_event->minimal_signups_activated) {
                    $signups .= " (" . $activity_event->minimal_signups . " req)";
                }

                $signups .= $event->getActivityDetailsView($act->id);
                $subView->addContent('{##activity_signups##}',  $signups);
                
                break;
            case '3' : 
                $poll = new Activity_Poll();
                $activity_event = $poll->getActivity($act->id);
                
                if (isset($activity_event->comments_activated) AND $activity_event->comments_activated == '1') {
                    $allow_comments = TRUE;
                } else {
                    $allow_comments = FALSE;
                }

                $event_data = Parsedown::instance()->text($activity_event->description);
                $event_data .= $activity_event->date . " @ ";
                $event_data .= $activity_event->time;
                    
                $content = $event_data;
                if ($activity_event->minimal_signups_activated) {
                    $content .= " (" . $activity_event->minimal_signups . " req)";
                }

                $delete_link = '/activity/poll/delete/' . $act->id;
                $update_link = '/activity/poll/update/' . $act->id;
                $comment_link = '/comment/activity/view/' . $act->id;
                $details_link = '/activity/poll/details/' . $act->id;
                break;
        }
        $subView->addContent('{##activity_content##}',  $content);
        
        if (isset($act->userid)) {
            $identity = new Identity();
            $subView->addContent('{##activity_identity##}', $identity->getIdentityById($act->userid, 0));
            $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($act->userid));
        }

        if ($allow_comments === TRUE) {
            $comment = new Comment();
            $comment_count = $comment->getCommentCount($act->id);

            $visitorView = new View();
            $visitorView->setTmpl($view->getSubTemplate('{##activity_not_logged_in##}'));
            $visitorView->addContent('{##comment_link##}', $comment_link);
            $visitorView->addContent('{##comment_link_text##}',  'comments (' . $comment_count . ')');
            $visitorView->replaceTags();
            $subView->addContent('{##activity_not_logged_in##}',  $visitorView);
        } else {
            $subView->addContent('{##activity_not_logged_in##}',  '');
        }
        
        $login = new Login();
        if ($login->isLoggedIn() AND isset($act->userid)) {
            if ($login->currentUserID() === $act->userid) {
                $memberView = new View();
                $memberView->setTmpl($view->getSubTemplate('{##activity_logged_in##}'));
                $memberView->addContent('{##delete_link##}', $delete_link);
                $memberView->addContent('{##delete_link_text##}',  'delete');
                $memberView->addContent('{##update_link##}', $update_link);
                $memberView->addContent('{##update_link_text##}',  'update');
                $memberView->replaceTags();
            } else {
                $memberView = '';
            }
            $subView->addContent('{##activity_logged_in##}',  $memberView);
        }

        if ($details_link !== '') {
            $linkView = new View();
            $linkView->setTmpl($view->getSubTemplate('{##details_link_area##}'));

            $linkView->addContent('{##details_link##}', $details_link);
            $linkView->addContent('{##details_link_text##}',  Parsedown::instance()->text($activity_event->title));
            $linkView->replaceTags();
        } else {
            $linkView = '';
        }
        $subView->addContent('{##details_link_area##}',  $linkView);

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
        $view->setTmpl($view->loadFile('/views/activity/list_all_activities.php'));
        $activities = ($type === NULL) ? $this->getActivities(10) : $this->getActivities();

        if (false !== $activities) {
            $activity_loop = NULL;
            foreach ($activities as $act) {
                if ($type !== NULL AND $act->type !== $type) {
                    continue;
                }
                
                $subView = $this->getSubView($act, $view, $compact = TRUE);
                
                $activity_loop .= $subView;
            }
            $view->addContent('{##activity_loop##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }

    function getActivityView($id = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/list_all_activities.php'));
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
