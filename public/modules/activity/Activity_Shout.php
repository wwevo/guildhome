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
class Activity_Shout extends Activity {

    function initEnv() {
        Toro::addRoute(["/activities/shouts" => "Activity_Shout"]);
        Toro::addRoute(["/activity/shout/:alpha" => "Activity_Shout"]);
        Toro::addRoute(["/activity/shout/:alpha/:alpha" => "Activity_Shout"]);
    }

    function create_tables() {
        $db = db::getInstance();
        $sql = "INSERT INTO activity_types (id, name, description)
                            VALUES('1', 'shout', 'a shout');";
        $result = $db->query($sql);
        echo $sql;
        $sql = "CREATE TABLE activity_shouts (
            activity_id INT(6) UNIQUE,
            content TEXT
        )";
        $result = $db->query($sql);
        echo $sql;
    }
    
    function get($alpha = '', $id = NULL) {
        $login = new Login();
        switch ($alpha) {
            default:
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>All shouts</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getAllActivitiesView('1')); // 1 = shout
                break;
            case 'new' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>New shout</h2>');
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
                $page->setContent('{##main##}', '<h2>Delete shout</h2>');
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
            case 'new' :
                if ($this->validateActivity() === true) {
                    if ($this->saveActivity() === true) {
                        header("Location: /activities");
                    }
                } else {
                    $this->get('new', $id);
                }
                break;
            case 'update' :
                if ($this->validateActivity() === true) {
                    if ($this->updateActivity($id) === true) {
                        header("Location: /activities");
                    }
                } else {
                    $this->get('update', $id);
                }
                break;
            case 'delete' :
                if (isset($env->post('activity')['submit'])) {
                    if ($env->post('activity')['submit'] === 'delete') {
                        if ($this->deleteActivity($id) === true) {
                            header("Location: /activities");
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities");
                    }
                }
                break;
        }
    }
    
    function getActivity($id) {
        $db = db::getInstance();
        $sql = "SELECT activity_shouts.content AS content, activities.userid AS userid
                    FROM activity_shouts
                    INNER JOIN activities
                    ON activities.id = activity_shouts.activity_id
                    WHERE activity_shouts.activity_id = '$id'
                    LIMIT 1;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activity = $result_row;
            }
            return $activity;
        }
        return false;
    }
    
    function getNewActivityForm() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        $view->setTmpl(file('views/activity/new_activity_shout_form.php'), array(
            '{##form_action##}' => '/activity/shout/new',
            '{##activity_content##}' => $env->post('activity')['content'],
            '{##activity_content_validation##}' => $msg->fetch('activity_shout_content_validation'),
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => 'say it loud',
        ));
        $view->replaceTags();
        return $view;
    }
    
    function getUpdateActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);
        $content = (isset($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->content;
        
        $view = new View();
        $view->setTmpl(file('views/activity/update_activity_shout_form.php'), array(
            '{##form_action##}' => '/activity/shout/update/' . $id,
            '{##activity_content##}' => $content,
            '{##activity_content_validation##}' => $msg->fetch('activity_shout_content_validation'),
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => "i'm sure now!",
        ));
        $view->replaceTags();
        return $view;
    }
    
    function getDeleteActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);
        $content = $act->content;
        
        $view = new View();
        $view->setTmpl(file('views/activity/delete_activity_form.php'), array(
            '{##form_action##}' => '/activity/shout/delete/' . $id,
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
        if (empty($env->post('activity')['content'])) {
            $msg->add('activity_shout_content_validation', 'Say something!! Please :)');
            $errors = true;
        }

        if ($errors === false) {
            return true;
        }
        return false;
    }
    
    function saveActivity() {
        $db = db::getInstance();
        $env = Env::getInstance();
        
        // save activity meta data
        $this->save($type=1); // 0=shout

        // save 'shout' specific data
        $activity_id = $db->insert_id;
        $content = $env->post('activity')['content'];
        $sql = "INSERT INTO activity_shouts (activity_id, content) VALUES ('$activity_id', '$content');";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clear_post('activity');
            return true;
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
        
        $content = $env->post('activity')['content'];
        $sql = "UPDATE activity_shouts SET
                        content = '$content'
                    WHERE activity_id = '$id';";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clear_post('activity');
            return true;
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
        $sql = "DELETE FROM activity_shouts 
                    WHERE activity_id = '$id';";
        $query = $db->query($sql);
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
$activity_shout = new Activity_Shout();
$activity_shout->initEnv();
