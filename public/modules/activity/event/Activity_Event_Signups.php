<?php

class Activity_Event_Signups {
    // start controller
    function initEnv() {
        Toro::addRoute(["/activity/event/signups/new" => "Activity_Event_Signups"]);
        Toro::addRoute(["/activity/event/signups/:alpha/:number" => "Activity_Event_Signups"]);
        
        Env::registerHook('event_signups', array(new Activity_Event_Signups(), 'getSignupsFormView'));
        Env::registerHook('activity_event_view_hook', array(new Activity_Event_Signups(), 'activityEventSignupsViewHook'));
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
                header("Location: " .  $env->post('target_url'));
                exit;
            case 'update' :
                if (isset($env->post('activity')['submit']['signups'])) {
                    if ($this->saveSignups($id)) {
                    }
                    $activity_event = new Activity_Event();
                    header("Location: /activity/event/update/" . $id);
                    exit;
                }
                break;
        }
    }

    function getActivitySignupsView($event_id) {
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivity($event_id);

        $signups_checked = $act->signups_activated;
        if ($signups_checked == '1') {
            $signed_up_users = $this->getSignupsByEventId($event_id);
            if (is_array($signed_up_users)) {
                $identity = new Identity();
                foreach ($signed_up_users as $key => $user_id) {
                    $signed_up_users[$key] = $identity->getIdentityById($user_id, 0);
                }
                $signed_up_users = implode(', ', $signed_up_users);
            } else {
                $signed_up_users = 'No signups so far! Be the first!';
            }
            $view = new View();
            $view->setTmpl($view->loadFile('/views/activity/event/activity_event_signups_view.php'), array(
                '{##signups##}' => $signed_up_users,
            ));

            $view->replaceTags();
            return $view;
        }
        return false;
    }
    
    function getSignupForm($event_id, $target_url = '') {
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivity($event_id);

        $login = new Login();
        if (!$login->isLoggedIn() OR $act->signups_activated != '1' OR $activity_event->eventIsCurrent($act) === false) {
            return false;
        }
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/activity_event_signup_button.php'));        
        $view->setContent('{##signup##}', '/activity/event/signups/signup/' . $event_id);
        if ($this->isSignedUp($event_id)) {
            $view->addContent('{##signup_text##}', 'Signout');
        } else {
            $view->addContent('{##signup_text##}', 'Signup');
        }
            $view->addContent('{##target_url##}', $target_url);
        $view->replaceTags();
        return $view;
    }

    function getSignupsFormView($id, $target_url = '') {
        $msg = Msg::getInstance();
        $env = Env::getInstance();
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/activity_signups_form.php'));
        
        $view->addContent('{##form_action##}', '/activity/event/signups/update/' . $id);
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivity($id);
        $signups_min_val = (!empty($env->post('activity')['signups_min_val'])) ? $env->post('activity')['signups_min_val'] : $act->minimal_signups;
        $signups_max_val = (!empty($env->post('activity')['signups_max_val'])) ? $env->post('activity')['signups_max_val'] : $act->maximal_signups;
        $signups_checked = (!empty($env->post('activity')['signups'])) ? $env->post('activity')['signups'] : $act->signups_activated;
        $signups_min_checked = (!empty($env->post('activity')['signups_min'])) ? $env->post('activity')['signups_min'] : $act->minimal_signups_activated;
        $signups_max_checked = (!empty($env->post('activity')['signups_max'])) ? $env->post('activity')['signups_max'] : $act->maximal_signups_activated;
        $keep_signups_open_checked = (!empty($env->post('activity')['keep_signups_open'])) ? $env->post('activity')['keep_signups_open'] : $act->signup_open_beyond_maximal;

        $view->addContent('{##target_url##}',$target_url);
        $view->addContent('{##activity_signups_checked##}', ($signups_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##activity_signups_min_checked##}', ($signups_min_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##signups_min_val##}', $signups_min_val);
        $view->addContent('{##activity_signups_max_checked##}', ($signups_max_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##signups_max_val##}', $signups_max_val);
        $view->addContent('{##activity_keep_signups_open_checked##}', ($keep_signups_open_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##submit_text##}', 'Update');
        $view->replaceTags();
        
        return $view;        
    }
    
    function isSignedUp($event_id) {
        $db = db::getInstance();
        $login = new Login();
        $user_id = $login->currentUserID();
        $sql = "SELECT * FROM activity_events_signups_user WHERE user_id = '$user_id' AND event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            return true;
        } else {
            return false;
        }
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

    function saveSignups($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $signups_min = isset($env->post('activity')['signups_min']) ? '1' : '0';
        $signups_max = isset($env->post('activity')['signups_max']) ? '1' : '0';
        $signups_min_val = !empty($env->post('activity')['signups_min_val']) ? $env->post('activity')['signups_min_val'] : '0';
        $signups_max_val = !empty($env->post('activity')['signups_max_val']) ? $env->post('activity')['signups_max_val'] : '0';
        $keep_signups_open = isset($env->post('activity')['keep_signups_open']) ? '1' : '0';
        $allow_signups = isset($env->post('activity')['signups']) ? '1' : '0';

        $sql = "UPDATE activity_events SET
                signups_activated = '$allow_signups'
            WHERE activity_id = '$activity_id';";
        $query = $db->query($sql);

        $sql = "SELECT * FROM activity_events_signups WHERE event_id = '$activity_id';";
        $db->query($sql);
        if ($db->affected_rows == 0) {
            $sql = "INSERT INTO activity_events_signups (event_id, minimal_signups_activated, minimal_signups, maximal_signups_activated, maximal_signups, signup_open_beyond_maximal, preference_selection_enabled) VALUES ('$activity_id', '$signups_min', '$signups_min_val', '$signups_max', '$signups_max_val', '$keep_signups_open', '0');";
        } else {
            $sql = "UPDATE activity_events_signups
                        SET 
                            minimal_signups_activated = '$signups_min',
                            minimal_signups = '$signups_min_val',
                            maximal_signups_activated = '$signups_max',
                            maximal_signups = '$signups_max_val',
                            signup_open_beyond_maximal = '$keep_signups_open',
                            preference_selection_enabled = '0'
                        WHERE event_id = '$activity_id';";
        }
        if ($db->query($sql)) {
            return true;
        }
        return false;
    }
    
    function toggleSignup($user_id, $event_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE user_id = '$user_id' AND event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $signup = FALSE;
            $sql = "DELETE FROM activity_events_signups_user 
                        WHERE user_id = '$user_id' AND event_id = '$event_id';";
            $query = $db->query($sql);        
        } else {
            $signup = TRUE;
            $sql = "INSERT INTO activity_events_signups_user (event_id, user_id, registration_id, preferred) VALUES ('$event_id', '$user_id', '', '0');";
            $query = $db->query($sql);        
        }

        if ($query !== false) {
            $env = Env::getInstance();
            if (isset($env::$hooks['toggle_event_signup_hook'])) {
                $env::$hooks['toggle_event_signup_hook']($event_id, $signup);
            }

            return true;
        }
        return false;
    }    
    
    function activityEventSignupsViewHook(&$loopView, $act, $event_id, $compact = false) {
        $signups = '';
        if ($act->signups_activated) {
            $signups = "Signed up:" . $this->getSignupCountByEventId($event_id);
        }

        if ($act->maximal_signups_activated) {
            $signups .= "/" . $act->maximal_signups;
        }

        if ($act->minimal_signups_activated) {
            $signups .= " (" . $act->minimal_signups . " required)";
        }

        $loopView->addContent('{##activity_signups##}',  $signups);
        $loopView->addContent('{##activity_signup_form##}', $this->getSignupForm($event_id, '/activity/event/details/' . $event_id));
        $loopView->addContent('{##activity_signups_list##}', $this->getActivitySignupsView($event_id));
        if (is_null($compact)) {
            $loopView->addContent('{##activity_detailed_signups_list##}', $this->getActivitySignupsView($event_id));
        }
    }
}
$activity_event_signups = new Activity_Event_Signups();
$activity_event_signups->initEnv();
unset($activity_event_signups);