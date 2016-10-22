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
class Activity_ActionMsg extends Activity {

    function initEnv() {
        // hooks for the various places where messages can be implemented
        Env::registerHook('save_comment_hook', array(new Activity_ActionMsg(), 'saveCommentAction'));
        Env::registerHook('new_user_hook', array(new Activity_ActionMsg(), 'saveNewUserAction'));
        Env::registerHook('toggle_event_signup_hook', array(new Activity_ActionMsg(), 'toggleEventSignupAction'));
        
        // hook for the activity module
        Env::registerHook('actnmsg', array(new Activity_ActionMsg(), 'getActivityView'));
    }
    
    function getActivityById($id = NULL) {
        $db = db::getInstance();
        $sql = "SELECT activity_actionmsg.*, from_unixtime(activities.create_time, '%Y-%m-%d') AS create_date,
                    from_unixtime(activities.create_time, '%H:%i') AS create_time
                    FROM activity_actionmsg
                    INNER JOIN activities
                        ON activities.id = activity_actionmsg.activity_id
                    WHERE activity_actionmsg.activity_id = $id
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
    
    function getActivityView($id = NULL, $compact = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/action_msg_view.php'));

        $actionmsg = $this->getActivityById($id);

        $message = 'at ' . $actionmsg->create_time . ', ';
        $message .= $actionmsg->message;
        if (isset($actionmsg->related_activity_id)) {
            $message .= ' (<a href="/comment/activity/view/'.$actionmsg->related_activity_id.'">view</a>)';
        }
        $view->setContent('{##action_message##}', $message);
        $view->replaceTags();

        return $view;    
    }

    function saveCommentAction($activity_id = NULL) {
        $db = db::getInstance();

        $this->save($type = '4'); // save metadata as action messages are activities
        $actionmsg_id = $db->insert_id;
        
        $activity = parent::getActivityById($activity_id);
        $actionmsg = parent::getActivityById($actionmsg_id);
        $identity = new Identity();
        $profile = new Profile();
        $message  = '<a href="' . $profile->getProfileUrlById($actionmsg->userid) . '">' . $identity->getIdentityById($actionmsg->userid) . '</a>';
        $message .= ' commented on ' . $activity->type_description;
                
        $sql = "INSERT INTO activity_actionmsg (activity_id, message, related_activity_id) VALUES ('$actionmsg_id', '$message', '$activity_id');";
        $db->query($sql);        
    }

    function toggleEventSignupAction($activity_id = NULL, $signup = FALSE) {
        $db = db::getInstance();

        $this->save($type = '4'); // save metadata as action messages are activities
        $actionmsg_id = $db->insert_id;
        
        $activity = parent::getActivityById($activity_id);
        $actionmsg = parent::getActivityById($actionmsg_id);
        $identity = new Identity();
        $profile = new Profile();
        $message  = '<a href="' . $profile->getProfileUrlById($actionmsg->userid) . '">' . $identity->getIdentityById($actionmsg->userid) . '</a>';
        if ($signup === TRUE) {
            $message .= ' signed up for ' . $activity->type_description;
        } else {
            $message .= ' signed out from ' . $activity->type_description;
        }
                
        $sql = "INSERT INTO activity_actionmsg (activity_id, message, related_activity_id) VALUES ('$actionmsg_id', '$message', '$activity_id');";
        $db->query($sql);        
    }


    function saveNewUserAction($user_id = NULL) {
        $db = db::getInstance();
        $this->save($type = '4'); // save metadata as action messages are activities
        $actionmsg_id = $db->insert_id;
        $identity = new Identity();
        $message = $identity->getIdentityById($user_id, 0) . ' created an account';

        $sql = "INSERT INTO activity_actionmsg (activity_id, message) VALUES ('$actionmsg_id', '$message');";
        $db->query($sql);        
    }
    
}
$activity_actionmsg = new Activity_ActionMsg();
$activity_actionmsg->initEnv();
