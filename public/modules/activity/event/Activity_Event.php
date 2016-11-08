<?php

class Activity_Event extends Activity {
    // start controller
    function initEnv() {
        Toro::addRoute(["/activities/events" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha/:number" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha" => "Activity_Event"]);
        
        Env::registerHook('event', array(new Activity_Event(), 'getActivityView'));
    }

    function get($action = '', $event_id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        $page = Page::getInstance();
        $page->addContent('{##main##}', parent::activityMenu('event'));
        switch ($action) {
            default :
                $env->clearPost('activity');
                $page->addContent('{##main##}', $this->getAllActivitiesView('2')); // 2 = event 
                break;
            case 'details' :
                if ($event_id === NULL OR !is_numeric($event_id)) {
                    break;
                }
                $page->addContent('{##main##}', '<h2>Event details</h2>');
                $page->addContent('{##main##}', $this->getActivityView($event_id));
                $act = new Activity();
                $comments = new Comment();
                if ($login->isLoggedIn() AND $act->commentsEnabled($event_id)) {
                    $page->addContent('{##main##}', $comments->getNewCommentForm($event_id));
                }
                if ($act->commentsEnabled($event_id)) {
                    $page->addContent('{##main##}', $comments->getAllCommentsView($event_id));
                }
                break;
            case 'new' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>New event</h2>');
                $page->addContent('{##main##}', $this->getActivityForm());
                if (isset($env->post('activity')['preview'])) {
                    $page->addContent('{##main##}', $this->getActivityPreview());
                }
                break;
            case 'update' :
                if (!$login->isLoggedIn() AND $event_id === NULL OR !is_numeric($event_id)) {
                    break;
                }
                $page->addContent('{##main##}', '<h2>Update event</h2>');
                $page->addContent('{##main##}', $this->getActivityForm($event_id));
                if (isset($env->post('activity')['preview'])) {
                    $page->addContent('{##main##}', $this->getActivityPreview());
                }
                break;
            case 'delete' :
                if (!$login->isLoggedIn() AND $event_id === NULL OR !is_numeric($event_id)) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>Delete event</h2>' . $event_id);
                $page->addContent('{##main##}', $this->getDeleteActivityForm($event_id));
                break;
        }
    }

    function post($action, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($action) {
            case 'new' :
                if ($this->validateActivity() === true AND !isset($env->post('activity')['preview'])) {
                    if (($event_id = $this->saveActivity()) !== false) {
                        header("Location: /activity/event/update/$event_id");
                        exit;
                    }
                }
                $this->get('new');
                break;
            case 'update' :
                if ($this->validateActivity() === true AND !isset($env->post('activity')['preview'])) {
                    if ($this->saveActivity($id) !== false) {
                        header("Location: /activity/event/update/$id");
                        exit;
                    }
                }
                $this->get('update', $id);
                break;
            case 'delete' :
                if (isset($env->post('activity')['submit']) AND $env->post('activity')['submit'] == 'delete') {
                    if ($this->deleteActivity($id) !== false) {
                        $env->clearPost('activity');
                        header("Location: /activities/events");
                        exit;
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities/events");
                        exit;
                    }
                }
                break;
        }
    }
    // end controller    
    // start model
    function getActivity($id) {
        $db = db::getInstance();

        $sql = "SELECT
                    a.comments_enabled AS comments_enabled,
                    a.userid AS userid,
                    a.type AS activity_type,
                    ae.activity_id AS activity_id,
                    ae.title AS title,
                    ae.description AS description,
                    ae.date AS date,
                    ae.time AS time,
                    ae.signups_activated AS signups_activated,
                    aes.event_id AS eventid,
                    aes.minimal_signups_activated AS minimal_signups_activated,
                    aes.minimal_signups AS minimal_signups,
                    aes.maximal_signups_activated AS maximal_signups_activated,
                    aes.maximal_signups AS maximal_signups,
                    aes.signup_open_beyond_maximal AS signup_open_beyond_maximal,
                    aes.class_registration_enabled AS class_registration_enabled,
                    aes.roles_registration_enabled,
                    aes.preference_selection_enabled AS preference_selection_enabled,
                    aet.name AS event_type,
                    IF(
                        DATE_ADD(concat(ae.date, ' ', ae.time), INTERVAL 2 HOUR) >= NOW()
                    AND
                        concat(ae.date, ' ', ae.time) <= DATE_ADD(NOW(), INTERVAL 48 HOUR),
                        'true', 'false'
                    ) as featured,
                    IF(
                        concat(ae.date, ' ', ae.time) >= DATE_ADD(NOW(), INTERVAL -1 HOUR)
                    AND
                        concat(ae.date, ' ', ae.time) <= DATE_ADD(NOW(), INTERVAL 1 HOUR),
                        'true', 'false'
                    ) as hot
                    FROM activity_events ae
                    LEFT JOIN activities a ON ae.activity_id = a.id
                    LEFT JOIN activity_events_signups aes ON ae.activity_id = aes.event_id
                    LEFT JOIN activity_events_types aet ON ae.event_type = aet.id
                    WHERE activity_id = '$id';";

        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity = $query->fetch_object();
            return $activity;
        }
        return false;
    }

    function saveActivity($event_id = NULL) { // : event_id : false
        $db = db::getInstance();
        $env = Env::getInstance();
        $title = $env->post('activity')['title'];
        $event_type = $env->post('activity')['event_type'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];

        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';

        if ($event_id === NULL) {
            $activity_id = $this->save($type = '2', $allow_comments);
            $sql = "INSERT INTO activity_events(activity_id, event_type, title, description, date, time) VALUES ($activity_id, '$event_type', '$title', '$description', '$date', '$time');";
            $query = $db->query($sql);
            if ($query !== false) {
                return $activity_id;
            }
        } else {
            $login = new Login();

            $userid = $login->currentUserID();
            $act = $this->getActivity($event_id);

            if ($userid != $act->userid) {
                return false;
            }

            $sql = "UPDATE activities SET
                            comments_enabled= '$allow_comments'
                        WHERE id = '$event_id';";
            $query = $db->query($sql);

            $sql = "UPDATE activity_events SET
                            title = '$title',
                            event_type = '$event_type',
                            description = '$description',
                            time = '$time',
                            date = '$date'
                        WHERE activity_id = '$event_id';";
            $query = $db->query($sql);

            if ($db->affected_rows > 0 OR $query !== false) {
                return $event_id;
            }
        }
        return false;
    }

    function eventIsCurrent($act) {
        $event_date = new DateTime($act->date . " " . $act->time);
        $current_date = new DateTime();
        if ($current_date > $event_date) {
            return false;
        }
        return true;
    }
    
    function deleteActivity($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $act = $this->getActivity($activity_id);
        if ($userid != $act->userid) {
            return false;
        }

        $sql = "UPDATE activities SET deleted = '1' WHERE id = '$activity_id';";
        
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
            if (isset($env::$hooks['delete_event_hook'])) {
                $env::$hooks['delete_event_hook']($activity_id);
            }
            return true;
        }
        return false;
    }

    function validateActivity() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();

        $errors = false;
        if (empty($env->post('activity')['title'])) {
            $msg->add('activity_event_title_validation', 'Gotta have a name for this baby!');
            $errors = true;
        }
        if (empty($env->post('activity')['content'])) {
            $msg->add('activity_event_content_validation', 'What is this about?');
            $errors = true;
        }
        if (empty($env->post('activity')['date'])) {
            $msg->add('activity_event_date_validation', 'When?');
            $errors = true;
        } elseif (empty($env->post('activity')['time'])) {
            $msg->add('activity_event_date_validation', 'When exactly?');
            $errors = true;
        }

        if ($errors === false) {
            return true;
        }
        return false;
    }
    // end model
    // start view
    function getActivityPreview() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/activity_event_view.php'));
        $view->setContent('{##activity_message##}', '<p>This is how your Event will look:</p>');

        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
        $subView->addContent('{##activity_published##}', date('Y-m-d H:i:s'));
        $subView->addContent('{##activity_type##}',  '<strong>an event</strong>');
        $subView->addContent('{##css##}', ' preview');
        $env = Env::getInstance();
        $content = Parsedown::instance()->text($env->post('activity')['content']);
        $subView->addContent('{##activity_content##}', $content);
        $login = new Login();
        $identity = new Identity();
        $subView->addContent('{##activity_identity##}', $identity->getIdentityById($login->currentUserID(), 0));
        $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($login->currentUserID()));
        $event_date = $env->post('activity')['date'] . " @ " . $env->post('activity')['time'];
        $subView->addContent('{##activity_event_date##}', $event_date);

        $details_link = '/activity/event/details/';
        $subView->addContent('{##details_link##}', View::linkFab($details_link, Parsedown::instance()->line($env->post('activity')['title'])));
        $subView->replaceTags();
        
        $view->addContent('{##activity_loop##}',  $subView);
        $view->replaceTags();
        return $view;
    }
    
    function getActivityView($event_id = NULL, $compact = NULL) {
        $env = Env::getInstance();
        $activityView = new View();
        $activityView->setTmpl($activityView->loadFile('/views/activity/event/activity_event_view.php'));

        $loopView = new View();
        $loopView->setTmpl($activityView->getSubTemplate('{##activity_loop##}'));

        $act = $this->getActivity($event_id);
        $act_meta = parent::getActivityById($event_id);
        
        if ($act === false OR $act->activity_type != '2') {
            return false;
        }

        if (isset($act_meta->create_time)) {
            $loopView->addContent('{##activity_published##}', $act_meta->create_time);
        }
        if (isset($act_meta->type_description)) {
            $loopView->addContent('{##activity_type##}',  $act_meta->type_description);
        }

        if ($act->featured === 'true') {
            $loopView->addContent('{##css##}', ' pulled_to_top');
        }
        if ($act->hot === 'true') {
            $loopView->addContent('{##css##}', ' hot');
        }
        if ($act_meta->deleted == '1') {
            $loopView->addContent('{##css##}', ' deleted');
        }

        $delete_link = '/activity/event/delete/' . $event_id;
        $update_link = '/activity/event/update/' . $event_id;
        $comment_link = '/comment/activity/view/' . $event_id;
        $details_link = '/activity/event/details/' . $event_id;

        $event_data = Parsedown::instance()->text($act->description);
        
        if (is_null($compact)) {
            $content = $event_data;
            $loopView->addContent('{##details_link##}', Parsedown::instance()->line($act->title));
        } else {
            $content = substr(strip_tags($event_data), 0, 150) . " ...";
            $loopView->addContent('{##link_more##}',  View::linkFab($details_link, '...more', 'more'));
            $loopView->addContent('{##details_link##}', View::linkFab($details_link, Parsedown::instance()->line($act->title)));
        }
        
        $loopView->addContent('{##activity_content##}',  $content);

        $event_date = $act->date . " @ " . $act->time;
        $loopView->addContent('{##activity_event_date##}', $event_date);
        $event_datetime = $act->date . " " . $act->time;
        $loopView->addContent('{##activity_event_datetime##}', $event_datetime);

        if (isset($act_meta->userid)) {
            $identity = new Identity();
            $loopView->addContent('{##activity_identity##}', $identity->getIdentityById($act_meta->userid, 0));
            $loopView->addContent('{##avatar##}', $identity->getAvatarByUserId($act_meta->userid));
        }

        if (isset($act->comments_enabled) AND $act->comments_enabled == '1') {
            $comment = new Comment();
            $comment_count = $comment->getCommentCount($event_id);
            $visitorView = new View();
            $visitorView->setTmpl($loopView->getSubTemplate('{##activity_not_logged_in##}'));
            $visitorView->addContent('{##comment_link##}', View::linkFab($comment_link, "comments ($comment_count)"));
            $visitorView->replaceTags();
            $loopView->addContent('{##activity_not_logged_in##}', $visitorView);
        }
        
        $login = new Login();
        if ($login->isLoggedIn() AND isset($act_meta->userid) AND $login->currentUserID() === $act_meta->userid) {
            $memberView = new View();
            $memberView->setTmpl($loopView->getSubTemplate('{##activity_logged_in##}'));
            $memberView->addContent('{##delete_link##}', View::linkFab($delete_link, 'delete'));
            $memberView->addContent('{##update_link##}', View::linkFab($update_link, 'update'));
            $memberView->replaceTags();
            $loopView->addContent('{##activity_logged_in##}',  $memberView);
        }

        if (isset($env::$hooks['activity_event_view_hook'])) {
            $env::$hooks['activity_event_view_hook']($loopView, $act, $event_id, $compact);
        }

        $loopView->replaceTags();

        $activityView->addContent('{##activity_loop##}', $loopView);
        $activityView->replaceTags();

        return $activityView;
    }
    
    function getActivityForm($event_id = NULL) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/activity_event_form.php'), array(
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
        ));

        if ($event_id === NULL) {
            $view->addContent('{##form_action##}', '/activity/event/new');
            $view->addContent('{##submit_text##}', 'Create');

            $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : '';
            $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : '';
            $date = (isset($env->post('activity')['date'])) ? $env->post('activity')['date'] : '';
            $time = (isset($env->post('activity')['time'])) ? $env->post('activity')['time'] : '';
            $comments_checked = (!empty($env->post('activity')['comments']) AND $env->post('activity')['comments'] !== NULL) ? '1' : '';
        } else {
            $view->addContent('{##form_action##}', '/activity/event/update/' . $event_id);
            $view->addContent('{##submit_text##}' , 'Update');

            $act = $this->getActivity($event_id);

            $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : $act->title;
            $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->description;
            $date = (isset($env->post('activity')['date'])) ? $env->post('activity')['date'] : $act->date;
            $time = (isset($env->post('activity')['time'])) ? $env->post('activity')['time'] : $act->time;
            $comments_checked = (!empty($env->post('activity')['comments'])) ? $env->post('activity')['comments'] : $act->comments_enabled;

            if (isset($env::$hooks['event_roles'])) {
                $event_data = $env::$hooks['event_roles']($event_id);
                $view->addContent('{##class_selection_form##}', $event_data);
            }

            if (isset($env::$hooks['event_signups'])) {
                $event_data = $env::$hooks['event_signups']($event_id);
                $view->addContent('{##signups_form##}', $event_data);
            }
        }
        
        $view->addContent('{##activity_title##}' , $title);
        $content = str_replace("\n\r", "&#13;", $content);
        $view->addContent('{##activity_content##}' , $content);
        $view->addContent('{##activity_date##}' , $date);
        $view->addContent('{##activity_time##}' , $time);
        $view->addContent('{##activity_comments_checked##}' , ($comments_checked === '1') ? 'checked="checked"' : '');

        $view->replaceTags();
        return $view;
    }
    
    function getDeleteActivityForm($id = NULL) {
        if ($id !== NULL) {
            $act = $this->getActivity($id);
            $content = $act->title . "<br />" . $act->description;
        } else {
            $content = '';
        }
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/delete_activity_form.php'), array(
            '{##form_action##}' => '/activity/event/delete/' . $id,
            '{##activity_content##}' => $content,
            '{##submit_text##}' => "delete",
            '{##cancel_text##}' => "cancel",
        ));
        $view->replaceTags();
        return $view;
    }

}
// end view
$activity_event = new Activity_Event();
$activity_event->initEnv();
unset($activity_event);