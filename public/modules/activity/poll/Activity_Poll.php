<?php

class Activity_Poll extends Activity {

    function initEnv() {
        Toro::addRoute(["/activities/polls" => "Activity_Poll"]);
        Toro::addRoute(["/activity/poll/:alpha" => "Activity_Poll"]);
        Toro::addRoute(["/activity/poll/:alpha/:alpha" => "Activity_Poll"]);

        Env::registerHook('poll', array(new Activity_Poll(), 'getActivityView'));
    }

    function get($action = '', $id = NULL) {
        $login = new Login();
        $page = Page::getInstance();
        $page->addContent('{##main##}', parent::activityMenu('poll'));
        return;
        
        switch ($action) {
            default :
                $page->addContent('{##main##}', '<h2>All polls</h2>');
                $page->addContent('{##main##}', $this->getAllActivitiesView('3')); // 3 = poll 
                break;
            case 'details' :
                $page->addContent('{##main##}', '<h2>Poll details</h2>');
                $page->addContent('{##main##}', $this->getActivityView($id));
                $page->addContent('{##main##}', $this->getActivityDetailsView($id));
                break;
            case 'new' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>New poll</h2>');
                $page->addContent('{##main##}', $this->getNewActivityForm());
                break;
            case 'update' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>Update poll</h2>');
                $page->addContent('{##main##}', $this->getUpdateActivityForm($id));
                break;
            case 'delete' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>Delete poll</h2>');
                $page->addContent('{##main##}', $this->getDeleteActivityForm($id));
                break;
        }
    }

    function post($action, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            header("Location: /activities/polls");
            exit;
        }
        return;
        
        switch ($action) {
            case 'vote' :
                if ($this->vote($login->currentUserID(), $id)) {
                    header("Location: /activities/poll/" . $id . "/results");
                    exit;
                } else {
                    header("Location: /activities/polls");
                    exit;
                }
                break;
            case 'new' :
                if ($this->validateActivity() === true) {
                    if ($this->saveActivity() === true) {
                        header("Location: /activities/polls");
                        exit;
                    }
                } else {
                    $this->get('new', $id);
                }
                break;
            case 'update' :
                if ($this->validateActivity() === true) {
                    if ($this->updateActivity($id) === true) {
                        header("Location: /activities/polls");
                        exit;
                    }
                } else {
                    $this->get('update', $id);
                }
                break;
            case 'delete' :
                if (isset($env->post('activity')['submit'])) {
                    if ($env->post('activity')['submit'] === 'delete') {
                        if ($this->deleteActivity($id) === true) {
                            header("Location: /activities/polls");
                            exit;
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities/polls");
                        exit;
                    }
                }
                break;
        }
    }
    
    function vote($user_id, $poll_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $sql = "SELECT * FROM activity_polls_signups_user WHERE user_id = '$user_id' AND event_id = '$poll_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            return false; // has already voted
        } else {
            $option_id = $env->post['activity']['option'];
            $sql = "INSERT INTO activity_polls_signups_user (activity_id, user_id, option_id) VALUES ('$poll_id', '$user_id', '$option_id');";
            $query = $db->query($sql);        
            if ($query !== false) {
                return true;
            }
        }
        return false;
    }
    
    function getSignupsByEventId($event_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_polls_signups_user WHERE event_id = '$event_id';";
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

        $sql = "";
        
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity = $query->fetch_object();

            return $activity;
        }
        return false;
    }

    function getActivityView($act = NULL, $compact = NULL) {
        $act = $this->getActivityById($act);

        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/poll/activity_poll_view'));

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
        $type_name = (isset($act->type_name)) ? $act->type_name : NULL;

        $activity_event = $this->getActivity($act->id);

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

        $subView->addContent('{##activity_content##}',  $content);
        
        if (isset($act->userid)) {
            $identity = new Identity();
            $subView->addContent('{##activity_identity##}', $identity->getIdentityById($act->userid, 0));
            $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($act->userid));
        }

        if ($allow_comments === TRUE) {
            $comment = new Activity_Comment();
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

        $view->addContent('{##activity_loop##}',  $subView);
        $view->replaceTags();

        return $view;
    }

    function getNewActivityForm() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $comments_checked = (!empty($env->post('activity')['comments']) AND $env->post('activity')['comments'] !== NULL) ? 'checked="checked"' : '';
        $signups_min_checked = (!empty($env->post('activity')['signups_min']) AND $env->post('activity')['signups_min'] !== NULL) ? 'checked="checked"' : '';
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/poll/activity_poll_form.php'), array(
            '{##form_action##}' => '/activity/poll/new',
            '{##activity_title##}' => $env->post('activity')['title'],
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content##}' => $env->post('activity')['content'],
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date##}' => $env->post('activity')['date'],
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time##}' => $env->post('activity')['time'],
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
            '{##activity_signups_min_checked##}' => $signups_min_checked,
            '{##signups_min_val##}' => $env->post('activity')['signups_min_val'],
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

        $comments_checked = (is_null($env->post('activity')['comments'])) ? $act->comments_activated : $env->post('activity')['comments'];
        $comments_checked = ($comments_checked === '1') ? 'checked="' . $comments_checked . '"' : '';

        $signups_min_checked = (is_null($env->post('activity')['signups_min'])) ? $act->minimal_signups_activated : $env->post('activity')['signups_min'];
        $signups_min_checked = ($signups_min_checked === '1') ? 'checked="' . $signups_min_checked . '"' : '';

        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/poll/activity_poll_form.php'), array(
            '{##form_action##}' => '/activity/poll/update/' . $id,
            '{##activity_title##}' => $title,
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content##}' => $content,
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date##}' => $date,
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time##}' => $time,
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
            '{##activity_signups_min_checked##}' => $signups_min_checked,
            '{##signups_min_val##}' => $signups_min_val,
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => 'Submit',
        ));
        $view->replaceTags();
        return $view;
    }
    
    function getActivityDetailsView($id) {
        $act = $this->getActivity($id);

        $identity = new Identity();
        $event_owner = $identity->getIdentityById($act->userid, 0);
        $profile = new Profile();
        $event_owner_profile = $profile->getProfileUrlById($act->userid);

        $signups_checked = $act->signups_activated;

        if ($signups_checked) {
            $signed_up_users = $this->getSignupsByEventId($id);
            if (is_array($signed_up_users)) {
                foreach ($signed_up_users as $key => $user_id) {
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
        $view->setTmpl($view->loadFile('/views/activity/poll/activity_poll_details_view.php'), array(
            '{##activity_owner##}' => $event_owner,
            '{##activity_owner_profile_url##}' => $event_owner_profile,
        ));
        
        $login = new Login();
        $signupsView = new View();
        $signupsView->setTmpl($view->getSubTemplate('{##signups_activated##}'));
        $signupsView->addContent('{##signups##}', $signed_up_users);


        if ($login->isLoggedIn() AND $act->signups_activated == 1) {
            $memberView = '';
            $memberView = new View();
            $memberView->setTmpl($signupsView->getSubTemplate('{##activity_logged_in##}'));
            $memberView->addContent('{##signup##}', '/activity/poll/signup/' . $id);
            $memberView->addContent('{##signup_text##}', 'Signup/out');
            $memberView->replaceTags();
            $signupsView->addContent('{##activity_logged_in##}',  $memberView);
            $signupsView->replaceTags();
        }
      
        $view->addContent('{##signups_activated##}',  $signupsView);
        $view->replaceTags();
        return $view;
    }
    
    function getDeleteActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);
        $content = $act->title . "<br />" . $act->description;
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/delete_activity_form.php'), array(
            '{##form_action##}' => '/activity/poll/delete/' . $id,
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
        $signups_min_val = !empty($env->post('activity')['signups_min_val']) ? $env->post('activity')['signups_min_val'] : '0';

        $sql = "SELECT * FROM activity_events_signups WHERE event_id = '$activity_id';";
        $query = $db->query($sql);
        if ($db->affected_rows == 0) {
            $sql = "INSERT INTO activity_events_signups (event_id, minimal_signups_activated, minimal_signups, maximal_signups_activated, maximal_signups, signup_open_beyond_maximal, class_registration_enabled, roles_registration_enabled, preference_selection_enabled) VALUES ('$activity_id', '$signups_min', '$signups_min_val', '0', '0', '0', '0', '0', '0');";
        } else {
            $sql = "UPDATE activity_events_signups
                        SET 
                            minimal_signups_activated = '$signups_min',
                            minimal_signups = '$signups_min_val',
                        WHERE event_id = '$activity_id';";
        }
        $query = $db->query($sql);
        if ($db->affected_rows > 0) {
            return true;
        }
        return false;
    }
    
    function getSignupsByUserId($user_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_polls_signups_user WHERE user_id = '$user_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $signups[] = $this->getActivity($result_row->event_id);
            }
            return $signups;
        }
        return false;
    }

    function getSignupsByUserIdView($user_id) {
        $signups = $this->getSignupsByUserId($user_id);
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/poll/activity_signups_view.php'));
        if (is_array($signups)) {
            $signups_loop = NULL;
            foreach ($signups as $signup) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##signups_loop##}'));
                $subView->addContent('{##activity_title##}', $signup->title);
                $subView->addContent('{##activity_details_link##}', '/activity/event/details/' . $signup->activity_id);
                $subView->addContent('{##activity_details_link_text##}', 'Event details');
                $subView->addContent('{##activity_event_date##}', $signup->date);
                $subView->addContent('{##activity_event_time##}', $signup->time);
                $subView->replaceTags();
                $signups_loop .= $subView;
            }
            $view->addContent('{##signups_loop##}',  $signups_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
    function getUpcomingActivities() {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_polls WHERE date >= DATE(NOW());";
        $query = $db->query($sql);

         if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $signups[] = $this->getActivity($result_row->activity_id);
            }

            return $signups;
        }
        return false;
    }
    
    function getUpcomingActivitiesView() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/scheduled_activities.php'));
        $act = $this->getUpcomingActivities();
        if (false !== $act && is_array($act)) {
            $activity_loop = NULL;
            foreach ($act as $activity) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
                $subView->addContent('{##activity_title##}', $activity->title);
                $subView->addContent('{##activity_details_link##}', '/activity/event/details/' . $activity->activity_id);
                $subView->addContent('{##activity_details_link_text##}', 'Event details');
                $subView->addContent('{##activity_event_date##}', $activity->date);
                $subView->addContent('{##activity_event_time##}', $activity->time);
                $subView->replaceTags();
                $activity_loop .= $subView;
            }
            $view->addContent('{##activity_loop##}',  $activity_loop);
        }
       
        $view->replaceTags();
        return $view;
    }
    
    function saveActivity() {
        $db = db::getInstance();
        $env = Env::getInstance();

        // save activity meta data
        $activity_id = $this->save($type = '3'); // 3=poll
        // save 'event' specific data
        
        $title = $env->post('activity')['title'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];

        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        $allow_signups = '1';
        
        $sql = "INSERT INTO activity_polls (activity_id, title, description, date, time, calendar_activated, comments_activated, signups_activated) VALUES ($activity_id, '$title', '$description', '$date', '$time', '0', '$allow_comments', '$allow_signups');";

        $query = $db->query($sql);
        if ($query !== false) {
            if ($this->saveSignups($activity_id) !== false) {
                $env->clearPost('activity');
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
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];
        
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        $allow_signups = '1';

        $sql = "UPDATE activity_polls
                    SET
                        title = '$title',
                        description = '$description',
                        time = '$time',
                        date = '$date',
                        comments_activated = '$allow_comments',
                        signups_activated = '$allow_signups'
                    WHERE activity_id = '$id';";

        $query = $db->query($sql);

//        echo $sql;
//        var_dump($query);
//        var_dump($db->affected_rows);
//        exit;

        if ($db->affected_rows > 0 OR $query !== false) {
            if ($this->saveSignups($id) !== false) {
                $env->clearPost('activity');
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
            $env->clearPost('activity');
            return true;
        }
        return false;
    }
}
$activity_poll = new Activity_Poll();
$activity_poll->initEnv();
unset($activity_poll);