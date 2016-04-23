<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Activity_Shout
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Activity_Event extends Activity {

    function initEnv() {
        Toro::addRoute(["/activities/events" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha/:alpha" => "Activity_Event"]);
    }

    function create_tables() {
        $db = db::getInstance();
        $sql = "INSERT INTO activity_types (id, name, description)
                            VALUES('2', 'event', 'an event');";
        $result = $db->query($sql);
    }

    function get($alpha = '', $id = NULL) {
        $login = new Login();
        switch ($alpha) {
            default :
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>All events</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getAllActivitiesView('2')); // 2 = event 
                break;
            case 'details' :
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>Event details</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getActivityDetailsView($id)); // 2 = event 
                break;
            case 'new' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>New event</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getNewActivityForm());
                break;
            case 'update' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>Update shout</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getUpdateActivityForm($id));
                break;
            case 'delete' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>Delete event</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getDeleteActivityForm($id));
                break;
        }
    }

    function post($alpha, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($alpha) {
            case 'signup' :
            case 'signout' :
                    $this->toggleSignup($login->currentUserID(), $id);
                break;
            case 'new' :
                if ($this->validateActivity() === true) {
                    if ($this->saveActivity() === true) {
                        header("Location: /activities/events");
                    }
                } else {
                    $this->get('new', $id);
                }
                break;
            case 'update' :
                if ($this->validateActivity() === true) {
                    if ($this->updateActivity($id) === true) {
                        header("Location: /activities/events");
                    }
                } else {
                    $this->get('update', $id);
                }
                break;
            case 'delete' :
                if (isset($env->post('activity')['submit'])) {
                    if ($env->post('activity')['submit'] === 'delete') {
                        if ($this->deleteActivity($id) === true) {
                            header("Location: /activities/events");
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities/events");
                    }
                }
                break;
        }
    }
    
    function toggleSignup($user_id, $event_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE user_id = '$user_id' AND event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $sql = "DELETE FROM activity_events_signups_user 
                        WHERE user_id = '$user_id' AND event_id = '$event_id';";
            $query = $db->query($sql);        
        } else {
            $sql = "INSERT INTO activity_events_signups_user (event_id, user_id, registration_id, preferred) VALUES ('$event_id', '$user_id', '', '0');";
            $query = $db->query($sql);        
        }

        if ($query !== false) {
            return true;
        }
        return false;
    }
    
    function getSignupCountByEventId($event_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            return $query->num_rows;
        }
        return '0';
    }
    
    function getSignupsByEventId($event_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $signups[] = $result_row->user_id;
            }
            return $signups;
        }
        return false;
    }

    function getActivity($id) {
        $db = db::getInstance();

        $sql = "SELECT ae.title AS title, ae.description AS description, ae.date AS date, ae.time AS time, ae.comments_activated AS comments_activated, 
                    ae.signups_activated AS signups_activated, a.userid AS userid, aes.event_id, aes.minimal_signups_activated AS minimal_signups_activated, aes.minimal_signups AS minimal_signups, 
                    aes.maximal_signups_activated AS maximal_signups_activated, aes.maximal_signups AS maximal_signups, aes.signup_open_beyond_maximal AS signup_open_beyond_maximal, 
                    aes.class_registration_enabled AS class_registration_enabled,aes.roles_registration_enabled, aes.preference_selection_enabled AS preference_selection_enabled
                    FROM activity_events ae
                    LEFT JOIN activities a ON ae.activity_id = a.id 
                    LEFT JOIN activity_events_signups aes ON ae.activity_id = aes.event_id
                    WHERE ae.activity_id = '$id';";
        
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity = $query->fetch_object();

            return $activity;
        }
        return false;
    }

    function getNewActivityForm() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $comments_checked = (!empty($env->post('activity')['comments']) AND $env->post('activity')['comments'] !== NULL) ? 'checked="checked"' : '';
        $signups_checked = (!empty($env->post('activity')['signups']) AND $env->post('activity')['signups'] !== NULL) ? 'checked="checked"' : '';
        $signups_min_checked = (!empty($env->post('activity')['signups_min']) AND $env->post('activity')['signups_min'] !== NULL) ? 'checked="checked"' : '';
        $signups_max_checked = (!empty($env->post('activity')['signups_max']) AND $env->post('activity')['signups_max'] !== NULL) ? 'checked="checked"' : '';
        $keep_signups_open_checked = (!empty($env->post('activity')['keep_signups_open']) AND $env->post('activity')['keep_signups_open'] !== NULL) ? 'checked="checked"' : '';

        $view = new View();
        $view->setTmpl(file('views/activity/new_activity_event_form.php'), array(
            '{##form_action##}' => '/activity/event/new',
            '{##activity_title##}' => $env->post('activity')['title'],
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content##}' => $env->post('activity')['content'],
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date##}' => $env->post('activity')['date'],
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time##}' => $env->post('activity')['time'],
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
            '{##activity_signups_checked##}' => $signups_checked,
            '{##activity_signups_min_checked##}' => $signups_min_checked,
            '{##signups_min_val##}' => $env->post('activity')['signups_min_val'],
            '{##activity_signups_max_checked##}' => $signups_max_checked,
            '{##signups_max_val##}' => $env->post('activity')['signups_max_val'],
            '{##activity_keep_signups_open_checked##}' => $keep_signups_open_checked,
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => 'Submit',
        ));
        $view->replaceTags();
        return $view;
    }

    function getUpdateActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);

        $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : $act->title;
        $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->description;
        $date = (!empty($env->post('activity')['date'])) ? $env->post('activity')['date'] : $act->date;
        $time = (!empty($env->post('activity')['time'])) ? $env->post('activity')['time'] : $act->time;
        $signups_min_val = (!empty($env->post('activity')['signups_min_val'])) ? $env->post('activity')['signups_min_val'] : $act->minimal_signups;
        $signups_max_val = (!empty($env->post('activity')['signups_max_val'])) ? $env->post('activity')['signups_max_val'] : $act->maximal_signups;

        $comments_checked = (is_null($env->post('activity')['comments'])) ? $act->comments_activated : $env->post('activity')['comments'];
        $comments_checked = ($comments_checked === '1') ? 'checked="' . $comments_checked . '"' : '';

        $signups_checked = (is_null($env->post('activity')['signups'])) ? $act->signups_activated : $env->post('activity')['signups'];
        $signups_checked = ($signups_checked === '1') ? 'checked="' . $signups_checked . '"' : '';

        $signups_min_checked = (is_null($env->post('activity')['signups_min'])) ? $act->minimal_signups_activated : $env->post('activity')['signups_min'];
        $signups_min_checked = ($signups_min_checked === '1') ? 'checked="' . $signups_min_checked . '"' : '';

        $signups_max_checked = (is_null($env->post('activity')['signups_max'])) ? $act->maximal_signups_activated : $env->post('activity')['signups_max'];
        $signups_max_checked = ($signups_max_checked === '1') ? 'checked="' . $signups_max_checked . '"' : '';

        $keep_signups_open_checked = (is_null($env->post('activity')['keep_signups_open'])) ? $act->signup_open_beyond_maximal : $env->post('activity')['keep_signups_open'];
        $keep_signups_open_checked = ($keep_signups_open_checked === '1') ? 'checked="' . $keep_signups_open_checked . '"' : '';

        $view = new View();
        $view->setTmpl(file('views/activity/update_activity_event_form.php'), array(
            '{##form_action##}' => '/activity/event/update/' . $id,
            '{##activity_title##}' => $title,
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content##}' => $content,
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date##}' => $date,
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time##}' => $time,
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
            '{##activity_signups_checked##}' => $signups_checked,
            '{##activity_signups_min_checked##}' => $signups_min_checked,
            '{##signups_min_val##}' => $signups_min_val,
            '{##activity_signups_max_checked##}' => $signups_max_checked,
            '{##signups_max_val##}' => $signups_max_val,
            '{##activity_keep_signups_open_checked##}' => $keep_signups_open_checked,
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => 'Submit',
        ));
        $view->replaceTags();
        return $view;
    }
    
    function getActivityDetailsView($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);

        $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : $act->title;
        $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->description;
        $date = (!empty($env->post('activity')['date'])) ? $env->post('activity')['date'] : $act->date;
        $time = (!empty($env->post('activity')['time'])) ? $env->post('activity')['time'] : $act->time;

        $comments_checked = $act->comments_activated;
        $signups_checked = $act->signups_activated;

        if ($signups_checked) {
            $signed_up_users = $this->getSignupsByEventId($id);
            if (is_array($signed_up_users)) {
                foreach ($signed_up_users as $key => $user_id) {
                    $identity = new Identity();
                    $signed_up_users[$key] = $identity->getIdentityById($user_id, 0);
                }
                $signed_up_users = implode(', ', $signed_up_users);
            } else {
                $signed_up_users = 'No signups so far! Be the first!';
            }
        } else {
            $signed_up_users = '';
        }
        
        $view = new View();
        $view->setTmpl(file('views/activity/activity_event_details_view.php'), array(
            '{##activity_title##}' => $title,
            '{##activity_content##}' => $content,
            '{##activity_date##}' => $date,
            '{##activity_time##}' => $time,
            '{##activity_comments_checked##}' => $comments_checked,
            '{##activity_signups_checked##}' => $signups_checked,
            '{##signups##}' => $signed_up_users,
        ));
        $login = new Login();
        $memberView = new View();
        $memberView->setTmpl($view->getSubTemplate('{##activity_logged_in##}'));
        if ($login->isLoggedIn() AND $act->signups_activated == 1) {
            $memberView->addContent('{##signup##}', '/activity/event/signup/' . $id);
            $memberView->addContent('{##signout##}', '/activity/event/signout/' . $id);
            $memberView->addContent('{##signup_text##}', 'Signup');
            $memberView->addContent('{##signout_text##}', 'Signout');
            $memberView->replaceTags();
            if ($login->currentUserID() === $act->userid) {
                $adminView = new View();
                $adminView->setTmpl($view->getSubTemplate('{##activity_admin##}'));
                $adminView->addContent('{##admin_content##}', 'Manage subscriptions');
                $adminView->replaceTags();
            } else {
                $adminView = '';
            }
        } elseif(!$login->isLoggedIn() AND $act->signups_activated == 1) {
            $memberView = 'Log in to signup';
            $adminView = '';
        } else {
            $memberView = '';
            $adminView = '';
        }
        $view->addContent('{##activity_logged_in##}',  $memberView);
        $view->addContent('{##activity_admin##}',  $adminView);
        
        $view->replaceTags();
        return $view;
    }
    
    function getDeleteActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);
        $content = $act->title . "<br />" . $act->description;
        
        $view = new View();
        $view->setTmpl(file('views/activity/delete_activity_form.php'), array(
            '{##form_action##}' => '/activity/event/delete/' . $id,
            '{##activity_content##}' => $content,
            '{##submit_text##}' => "delete",
            '{##cancel_text##}' => "cancel",
        ));
        $view->replaceTags();
        return $view;
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
        }
        if (empty($env->post('activity')['time'])) {
            $msg->add('activity_event_time_validation', 'When exactly?');
            $errors = true;
        }

        if ($errors === false) {
            return true;
        }
        return false;
    }
    
    function saveSignups($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $signups_min = isset($env->post('activity')['signups_min']) ? '1' : '0';
        $signups_max = isset($env->post('activity')['signups_max']) ? '1' : '0';
        $signups_min_val = !empty($env->post('activity')['signups_min_val']) ? $env->post('activity')['signups_min_val'] : '0';
        $signups_max_val = !empty($env->post('activity')['signups_max_val']) ? $env->post('activity')['signups_max_val'] : '0';
        $keep_signups_open = isset($env->post('activity')['keep_signups_open']) ? '1' : '0';

        $sql = "INSERT INTO activity_events_signups (event_id, minimal_signups_activated, minimal_signups, maximal_signups_activated, maximal_signups, signup_open_beyond_maximal, class_registration_enabled, roles_registration_enabled, preference_selection_enabled) VALUES ('$activity_id', '$signups_min', '$signups_min_val', '$signups_max', '$signups_max_val', '$keep_signups_open', '0', '0', '0');";
          //  echo $sql; exit;
        $query = $db->query($sql);
        if ($query !== false) {
            return true;
        }
        return false;
    }
    
    function updateSignups($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $signups_min = isset($env->post('activity')['signups_min']) ? '1' : '0';
        $signups_max = isset($env->post('activity')['signups_max']) ? '1' : '0';
        $signups_min_val = !empty($env->post('activity')['signups_min_val']) ? $env->post('activity')['signups_min_val'] : '0';
        $signups_max_val = !empty($env->post('activity')['signups_max_val']) ? $env->post('activity')['signups_max_val'] : '0';
        $keep_signups_open = isset($env->post('activity')['keep_signups_open']) ? '1' : '0';

        $sql = "UPDATE activity_events_signups
                    SET 
                        minimal_signups_activated = '$signups_min',
                        minimal_signups = '$signups_min_val',
                        maximal_signups_activated = '$signups_max',
                        maximal_signups = '$signups_max_val',
                        signup_open_beyond_maximal = '$keep_signups_open',
                        class_registration_enabled = '0',
                        roles_registration_enabled = '0',
                        preference_selection_enabled = '0'
                    WHERE event_id = '$activity_id';";
        $query = $db->query($sql);
        if ($db->affected_rows !== 0 OR $query !== false) {
            return true;
        } else {
            if ($this->saveSignups($activity_id) !== false) {
                return true;
            }
        } 
        return false;
    }
    
    function saveActivity() {
        $db = db::getInstance();
        $env = Env::getInstance();

        // save activity meta data
        $this->save($type = 2); // 2=event
        // save 'event' specific data
        $activity_id = $db->insert_id;
        
        $event_type = $env->post('activity')['event_type'];
        $title = $env->post('activity')['title'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];

        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        $allow_signups = isset($env->post('activity')['signups']) ? '1' : '0';
        
        $sql = "INSERT INTO activity_events (activity_id, event_type, title, description, date, time, calendar_activated, schedule_activated, comments_activated, signups_activated, template_activated) VALUES ($activity_id, '$event_type', '$title', '$description', '$date', '$time', '0', '0', '$allow_comments', '$allow_signups', '0');";
        $query = $db->query($sql);
        if ($query !== false) {
            if ($this->saveSignups($activity_id) !== false) {
                $env->clear_post('activity');
                return true;
            }
        }
        return false;
    }

    function updateActivity($id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $actid = $this->getActivity($id)->userid;

        if ($userid != $actid) {
            return false;
        }
        
        $title = $env->post('activity')['title'];
        $event_type = $env->post('activity')['event_type'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];
        
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        $allow_signups = isset($env->post('activity')['signups']) ? '1' : '0';

        $sql = "UPDATE activity_events
                    SET
                        title = '$title',
                        event_type = '$event_type',
                        description = '$description',
                        time = '$time',
                        date = '$date',
                        comments_activated = '$allow_comments',
                        signups_activated = '$allow_signups'
                    WHERE activity_id = '$id';";

        $query = $db->query($sql);
        if ($db->affected_rows !== 0 OR $query !== false) {
            if ($this->updateSignups($id) !== false) {
                $env->clear_post('activity');
                return true;
            }
        }
        return false;
    }
    
    function deleteActivity($id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $actid = $this->getActivity($id)->userid;
        if ($userid != $actid) {
            return false;
        }
        //$sql = "DELETE FROM activity_events WHERE activity_id = '$id';";
        //$query = $db->query($sql);
        $sql = "DELETE FROM activities 
                    WHERE id = '$id';";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clear_post('activity');
            return true;
        }
        return false;
    }
}

$activity_event = new Activity_Event();
$activity_event->initEnv();
