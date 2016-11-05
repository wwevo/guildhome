<?php

class Activity_Event_Roles {
    // start controller
    function initEnv() {
        Toro::addRoute(["/activity/event/roles/:alpha" => "Activity_Event_Roles"]);
        Toro::addRoute(["/activity/event/roles/:alpha/:alpha" => "Activity_Event_Roles"]);
        
        Env::registerHook('event_roles', array(new Activity_Event_Roles(), 'getClassSelectionFormView'));
    }
    
    function post($alpha, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($alpha) {
            case 'update' :
                if (isset($env->post('activity')['submit']['submit_add_role'])) {
                    if ($this->validateRole() === TRUE) {
                        $this->saveRole($env->post('activity')['add_role_title']);    
                    }
                }
                
                if (isset($env->post('activity')['submit']['delete_selected_roles'])) {
                    $selected_roles = $env->post('activity')['roles'];
                    $this->deleteRole($selected_roles);
                }
                        
                if (isset($env->post('activity')['submit']['save_roles'])) {
                    if ($this->saveSignupsRoles($id) !== false) {
                    }
                }
                header("Location: /activity/event/update/" . $id);
                exit;
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
        $selectable_roles = isset($env->post('activity')['selectable_roles']) ? '1' : '0';

        $sql = "SELECT * FROM activity_events_signups WHERE event_id = '$activity_id';";
        $db->query($sql);
        if ($db->affected_rows == 0) {
            $sql = "INSERT INTO activity_events_signups (event_id, preference_selection_enabled, class_registration_enabled, roles_registration_enabled) VALUES ('$activity_id', '0', '$class_registration', '$selectable_roles');";
        } else {
            $sql = "UPDATE activity_events_signups
                        SET 
                            class_registration_enabled = '$class_registration',
                            roles_registration_enabled = '$selectable_roles'
                        WHERE event_id = '$activity_id';";
        }
        if ($db->query($sql)) {
            return true;
        }
        return false;
    }

    function getClassSelectionFormView($id) {
        $msg = Msg::getInstance();
        $env = Env::getInstance();
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/activity_class_selection_form.php'));
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

        $activity_event = new Activity_Event();
        $act = $activity_event->getActivity($id);
        $view->addContent('{##form_action##}', '/activity/event/roles/update/' . $id);
        $class_registration_checked = (!empty($env->post('activity')['class_registration'])) ? $env->post('activity')['class_registration'] : $act->class_registration_enabled;
        $selectable_roles_checked = (!empty($env->post('activity')['selectable_roles'])) ? $env->post('activity')['selectable_roles'] : $act->roles_registration_enabled;
        
        $view->addContent('{##add_role_title_text##}', 'new role title');
        $view->addContent('{##add_new_role_title##}', '');
        $view->addContent('{##submit_add_role_text##}', 'add role');
        $view->addContent('{##delete_selected_roles_text##}', 'delete selected roles');
        $view->addContent('{##add_role_title_validation##}', $msg->fetch('activity_add_role_title_validation'));
        $view->addContent('{##activity_class_registration_checked##}', ($class_registration_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##activity_selectable_roles_checked##}', ($selectable_roles_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##submit_save_roles_text##}', 'Use selected Roles');
        $view->replaceTags();
        
        return $view;        
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
}
$activity_event_roles = new Activity_Event_Roles();
$activity_event_roles->initEnv();
