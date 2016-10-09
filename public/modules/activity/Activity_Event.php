<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Activity_Event
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Activity_Event extends Activity {

    function initEnv() {
        Toro::addRoute(["/activities/events" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha/:alpha" => "Activity_Event"]);
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
                
                $page->addContent('{##main##}', $this->getActivityView($id));
                $comments = new Comment();
                if ($login->isLoggedIn()) {
                    $page->addContent('{##main##}', $comments->getNewCommentForm($id));
                }
                $page->addContent('{##main##}', $comments->getAllCommentsView($id));

                break;
            case 'new' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>New event</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getActivityForm());
                break;
            case 'update' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>Update event</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getActivityForm($id));
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
                    header("Location: /activities/events");
                break;
            case 'new' :
                if (isset($env->post('activity')['submit']) AND isset($env->post('activity')['submit']['submit_add_role'])) {
                    if ($this->validateRole() === TRUE) {
                        $this->saveRole($env->post('activity')['add_role_title']);    
                    }
                    $this->get('new', $id);
                    break;
                }
                
                if (isset($env->post('activity')['submit']) AND isset($env->post('activity')['submit']['delete_selected_roles'])) {
                    $selected_roles = $env->post('activity')['roles'];
                    $this->deleteRole($selected_roles);
                    $this->get('new', $id);
                    break;    
                }

                if ($this->validateActivity() === true) {
                    if ($this->saveActivity() === true) {
                        header("Location: /activities/events");
                    }
                } else {
                    $this->get('new', $id);
                }
                break;
            case 'update' :
                if (isset($env->post('activity')['submit']) AND isset($env->post('activity')['submit']['submit_add_role'])) {
                    if ($this->validateRole() === TRUE) {
                        $this->saveRole($env->post('activity')['add_role_title']);    
                    }
                    $this->get('update', $id);
                    break;
                }
                
                if (isset($env->post('activity')['submit']) AND isset($env->post('activity')['submit']['delete_selected_roles'])) {
                    $selected_roles = $env->post('activity')['roles'];
                    $this->deleteRole($selected_roles);
                    $this->get('update', $id);
                    break;    
                }

                if ($this->validateActivity() === true) {
                    if ($this->saveActivity($id) === true) {
                        header("Location: /activity/event/details/$id");
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
    
    function saveRole($title, $user_id = NULL) {
        $db = db::getInstance();
        if (is_null($user_id)) {
            $login = new Login();
            $user_id = $login->currentUserID();
        }
        
        $sql = "INSERT INTO activity_events_roles (name, user_id) VALUES ('$title', '$user_id');";
        $query = $db->query($sql);        

        if ($query !== false) {
            return true;
        }
        return false;
    }

    function deleteRole($roles) {
        $db = db::getInstance();
        $login = new Login();
        $user_id = $login->currentUserID();
        
        $role_ids = implode(',', $roles);
        $sql = "DELETE FROM activity_events_roles 
                    WHERE id IN ($role_ids) AND user_id = '$user_id';";
        $query = $db->query($sql);        

        if ($query !== false) {
            return true;
        }
        return false;
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

    function getRoles() {
        $login = new Login();
        $user_id = $login->currentUserID();

        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_roles WHERE user_id = '$user_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $roles[] = $result_row;
            }
            return $roles;
        }
        return false;
    }

    function getSignupsRoles($event_id) {
        $login = new Login();
        $user_id = $login->currentUserID();

        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_roles WHERE event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $roles[$result_row->role_id] = $result_row->event_id;
            }
            return $roles;
        }
        return false;
    }

    function getActivity($id) {
        $db = db::getInstance();

        $sql = "SELECT ae.activity_id AS activity_id, ae.title AS title, ae.description AS description, ae.date AS date, ae.time AS time, ae.comments_activated AS comments_activated, 
                    ae.signups_activated AS signups_activated, a.userid AS userid, aes.event_id, aes.minimal_signups_activated AS minimal_signups_activated, aes.minimal_signups AS minimal_signups, 
                    aes.maximal_signups_activated AS maximal_signups_activated, aes.maximal_signups AS maximal_signups, aes.signup_open_beyond_maximal AS signup_open_beyond_maximal, 
                    aes.class_registration_enabled AS class_registration_enabled,aes.roles_registration_enabled, aes.preference_selection_enabled AS preference_selection_enabled,
                    aet.name AS event_type
                    FROM activity_events ae
                    LEFT JOIN activities a ON ae.activity_id = a.id 
                    LEFT JOIN activity_events_signups aes ON ae.activity_id = aes.event_id
                    LEFT JOIN activity_events_types aet ON ae.event_type = aet.id
                    WHERE ae.activity_id = '$id';";
        
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity = $query->fetch_object();

            return $activity;
        }
        return false;
    }

    function getActivityForm($id = NULL) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/activity_event_form.php'), array(
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
        ));

        if ($roles = $this->getRoles()) {
            $options_list = '';
            $signups_roles = $this->getSignupsRoles($id);

            foreach ($roles as $role) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##roles_select_option##}'));
                $subView->addContent('{##option_value##}', $role->id);
                $subView->addContent('{##option_text##}', $role->name);
                if (isset($signups_roles[$role->id])) {
                    $subView->addContent('{##option_selected##}', ' checked="checked"');
                }
                $subView->replaceTags();
                $options_list .= $subView;
            }
            $view->addContent('{##roles_select_option##}', $options_list);
        }
        $view->addContent('{##add_role_title_text##}', 'new role title');
        $view->addContent('{##add_new_role_title##}', '');
        $view->addContent('{##submit_add_role_text##}', 'add role');
        $view->addContent('{##delete_selected_roles_text##}', 'delete selected roles');
        $view->addContent('{##add_role_title_validation##}', $msg->fetch('activity_add_role_title_validation'));

        if ($id === NULL) {
            
            $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : '';
            $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : '';
            $date = (!empty($env->post('activity')['date'])) ? $env->post('activity')['date'] : '';
            $time = (!empty($env->post('activity')['time'])) ? $env->post('activity')['time'] : '';
            $signups_min_val = (!empty($env->post('activity')['signups_min_val'])) ? $env->post('activity')['signups_min_val'] : '';
            $signups_max_val = (!empty($env->post('activity')['signups_max_val'])) ? $env->post('activity')['signups_max_val'] : '';

            $comments_checked = (!empty($env->post('activity')['comments']) AND $env->post('activity')['comments'] !== NULL) ? '1' : '';
            $signups_checked = (!empty($env->post('activity')['signups']) AND $env->post('activity')['signups'] !== NULL) ? '1' : '';
            $signups_min_checked = (!empty($env->post('activity')['signups_min']) AND $env->post('activity')['signups_min'] !== NULL) ? '1' : '';
            $signups_max_checked = (!empty($env->post('activity')['signups_max']) AND $env->post('activity')['signups_max'] !== NULL) ? '1' : '';
            $keep_signups_open_checked = (!empty($env->post('activity')['keep_signups_open']) AND $env->post('activity')['keep_signups_open'] !== NULL) ? '1' : '';

            $class_registration_checked = (!empty($env->post('activity')['class_registration']) AND $env->post('activity')['class_registration'] !== NULL) ? '1' : '';
            $selectable_roles_checked = (!empty($env->post('activity')['selectable_roles']) AND $env->post('activity')['selectable_roles'] !== NULL) ? '1' : '';

            $view->addContent('{##form_action##}', '/activity/event/new');

            $view->addContent('{##preview_text##}', 'Preview');
            $view->addContent('{##draft_text##}', 'Save as draft');
            $view->addContent('{##submit_text##}', 'Create');
            
        } else {
            $act = $this->getActivity($id);

            $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : $act->title;
            $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->description;
            $date = (!empty($env->post('activity')['date'])) ? $env->post('activity')['date'] : $act->date;
            $time = (!empty($env->post('activity')['time'])) ? $env->post('activity')['time'] : $act->time;
            $signups_min_val = (!empty($env->post('activity')['signups_min_val'])) ? $env->post('activity')['signups_min_val'] : $act->minimal_signups;
            $signups_max_val = (!empty($env->post('activity')['signups_max_val'])) ? $env->post('activity')['signups_max_val'] : $act->maximal_signups;

            $comments_checked = (!empty($env->post('activity')['comments'])) ? $env->post('activity')['comments'] : $act->comments_activated;
            $signups_checked = (!empty($env->post('activity')['signups'])) ? $env->post('activity')['signups'] : $act->signups_activated;
            $signups_min_checked = (!empty($env->post('activity')['signups_min'])) ? $env->post('activity')['signups_min'] : $act->minimal_signups_activated;
            $signups_max_checked = (!empty($env->post('activity')['signups_max'])) ? $env->post('activity')['signups_max'] : $act->maximal_signups_activated;
            $keep_signups_open_checked = (!empty($env->post('activity')['keep_signups_open'])) ? $env->post('activity')['keep_signups_open'] : $act->signup_open_beyond_maximal;

            $class_registration_checked = (!empty($env->post('activity')['class_registration'])) ? $env->post('activity')['class_registration'] : $act->class_registration_enabled;
            $selectable_roles_checked = (!empty($env->post('activity')['selectable_roles'])) ? $env->post('activity')['selectable_roles'] : $act->roles_registration_enabled;

            $view->addContent('{##form_action##}', '/activity/event/update/' . $id);

            $view->addContent('{##preview_text##}' , 'Preview');
            $view->addContent('{##draft_text##}' , 'Save as draft');
            $view->addContent('{##submit_text##}' , 'Update');
        }
        
        $view->addContent('{##activity_title##}' , $title);
        $view->addContent('{##activity_content##}' , $content);
        $view->addContent('{##activity_date##}' , $date);
        $view->addContent('{##activity_time##}' , $time);
        $view->addContent('{##activity_comments_checked##}' , ($comments_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##activity_signups_checked##}' , ($signups_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##activity_signups_min_checked##}' , ($signups_min_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##signups_min_val##}' , $signups_min_val);
        $view->addContent('{##activity_signups_max_checked##}' , ($signups_max_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##signups_max_val##}' , $signups_max_val);
        $view->addContent('{##activity_keep_signups_open_checked##}' , ($keep_signups_open_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##activity_class_registration_checked##}' , ($class_registration_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##activity_selectable_roles_checked##}' , ($selectable_roles_checked === '1') ? 'checked="checked"' : '');
        $view->replaceTags();
        return $view;
    }
    
    function getActivityDetailsView($id) {
        $act = $this->getActivity($id);

        $identity = new Identity();
        $event_owner = $identity->getIdentityById($act->userid, 0);
        $profile = new Profile();
        $event_owner_profile = $profile->getProfileUrlById($act->userid);

        $event_type = $act->event_type;

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
        $view->setTmpl($view->loadFile('/views/activity/event/activity_event_details_view.php'), array(
            '{##activity_type##}' => $event_type,
            '{##activity_owner##}' => $event_owner,
            '{##activity_owner_profile_url##}' => $event_owner_profile,
        ));
        
        $login = new Login();
        $signupsView = new View();
        $signupsView->setTmpl($view->getSubTemplate('{##signups_activated##}'));
        $signupsView->addContent('{##signups##}', $signed_up_users);


        if ($login->isLoggedIn() AND $act->signups_activated == 1 AND $this->eventIsCurrent($act)) {
            $memberView = new View();
            $memberView->setTmpl($signupsView->getSubTemplate('{##activity_logged_in##}'));
            $memberView->addContent('{##signup##}', '/activity/event/signup/' . $id);
            $memberView->addContent('{##signup_text##}', 'Signup/out');
            $memberView->replaceTags();
            $signupsView->addContent('{##activity_logged_in##}',  $memberView);
            $signupsView->replaceTags();
        }
      
        $view->addContent('{##signups_activated##}',  $signupsView);
        $view->replaceTags();
        return $view;
    }
    
    function eventIsCurrent($act) {
        $event_date = new DateTime($act->date . " " . $act->time);
        $current_date = new DateTime();
        if ($current_date > $event_date) {
            return false;
        }
        return true;
    }
    
    function getDeleteActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);
        $content = $act->title . "<br />" . $act->description;
        
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
    
    function validateRole() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();

        $errors = false;
        if (empty($env->post('activity')['add_role_title'])) {
            $msg->add('activity_add_role_title_validation', 'Gotta have a name for this baby!');
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
        $class_registration = isset($env->post('activity')['class_registration']) ? '1' : '0';
        $selectable_roles = isset($env->post('activity')['selectable_roles']) ? '1' : '0';

        $sql = "SELECT * FROM activity_events_signups WHERE event_id = '$activity_id';";
        $query = $db->query($sql);
        if ($db->affected_rows == 0) {
            $sql = "INSERT INTO activity_events_signups (event_id, minimal_signups_activated, minimal_signups, maximal_signups_activated, maximal_signups, signup_open_beyond_maximal, class_registration_enabled, roles_registration_enabled, preference_selection_enabled) VALUES ('$activity_id', '$signups_min', '$signups_min_val', '$signups_max', '$signups_max_val', '$keep_signups_open', '$class_registration', '$selectable_roles', '0');";
        } else {
            $sql = "UPDATE activity_events_signups
                        SET 
                            minimal_signups_activated = '$signups_min',
                            minimal_signups = '$signups_min_val',
                            maximal_signups_activated = '$signups_max',
                            maximal_signups = '$signups_max_val',
                            signup_open_beyond_maximal = '$keep_signups_open',
                            class_registration_enabled = '$class_registration',
                            roles_registration_enabled = '$selectable_roles',
                            preference_selection_enabled = '0'
                        WHERE event_id = '$activity_id';";
        }
        if ($db->query($sql)) {
            return true;
        }
        return false;
    }
    
    function deleteSignupsRolesByEventId($event_id) {
        $db = db::getInstance();
        $sql = "DELETE FROM activity_events_signups_roles WHERE event_id = '$event_id';";
        $query = $db->query($sql);
        if ($query !== false) {
            return true;
        }
        return false;
    }

    function saveSignupsRoles($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $class_registration = isset($env->post('activity')['class_registration']) ? '1' : '0';
        $signups_roles = isset($env->post('activity')['roles']) ? $env->post('activity')['roles'] : false;
        
        $this->deleteSignupsRolesByEventId($activity_id);

        $error = false;
        foreach ($signups_roles as $role) {
            $sql = "INSERT INTO activity_events_signups_roles (role_id, event_id, name) VALUES ('$role', '$activity_id', '');";
            echo $sql;
            if ($db->query($sql) !== true) {
                $error = true;
            }
        }

        if ($error === false) {
            return true;
        }
        return false;
    }
    
    function getSignupsByUserId($user_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE user_id = '$user_id';";
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
        $view->setTmpl($view->loadFile('/views/activity/event/activity_signups_view.php'));
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
        $sql = "SELECT * FROM activity_events WHERE date >= DATE(NOW()) ORDER BY date;";
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
        $view->setTmpl($view->loadFile('/views/activity/event/scheduled_activities.php'));
        $act = $this->getUpcomingActivities();
        if (false !== $act && is_array($act)) {
            $activity_loop = NULL;
            foreach ($act as $activity) {
                $date = new DateTime($activity->date . ' ' . $activity->time);
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
                $subView->addContent('{##activity_title##}', $activity->title);
                $subView->addContent('{##activity_details_link##}', '/activity/event/details/' . $activity->activity_id);
                $subView->addContent('{##activity_details_link_text##}', 'Event details');
                $subView->addContent('{##activity_event_date##}', $date->format('Y-m-d'));
                $subView->addContent('{##activity_event_time##}', $date->format('H:i'));
                $subView->replaceTags();
                $activity_loop .= $subView;
            }
            $view->addContent('{##activity_loop##}',  $activity_loop);
        }
       
        $view->replaceTags();
        return $view;
    }
    
    function saveActivity($id = NULL) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $title = $env->post('activity')['title'];
        $event_type = $env->post('activity')['event_type'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];

        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        $allow_signups = isset($env->post('activity')['signups']) ? '1' : '0';

        if ($id === NULL) {
            $this->save($type = 2);
            $activity_id = $db->insert_id;
            $sql = "INSERT INTO activity_events (activity_id, event_type, title, description, date, time, calendar_activated, schedule_activated, comments_activated, signups_activated, template_activated) VALUES ($activity_id, '$event_type', '$title', '$description', '$date', '$time', '0', '0', '$allow_comments', '$allow_signups', '0');";
            $query = $db->query($sql);
            if ($query !== false) {
                if ($this->saveSignups($activity_id) !== false AND $this->saveSignupsRoles($activity_id) !== false) {
                    $env->clear_post('activity');
                    return true;
                }
            }
        } else {
            $login = new Login();

            $userid = $login->currentUserID();
            $actid = $this->getActivity($id)->userid;

            if ($userid != $actid) {
                return false;
            }

            $sql = "UPDATE activity_events SET
                            title = '$title',
                            event_type = '$event_type',
                            description = '$description',
                            time = '$time',
                            date = '$date',
                            comments_activated = '$allow_comments',
                            signups_activated = '$allow_signups'
                        WHERE activity_id = '$id';";

            $query = $db->query($sql);

            if ($db->affected_rows > 0 OR $query !== false) {
                if ($this->saveSignups($id) !== false AND $this->saveSignupsRoles($id) !== false) {
                    $env->clear_post('activity');
                    return true;
                }
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
