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
        
        Env::registerHook('shout', array(new Activity_Shout(), 'getActivityView'));
    }

    function get($alpha = '', $id = NULL) {
        $login = new Login();
        switch ($alpha) {
            default :
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
                        header("Location: /activities/shouts");
                    }
                } else {
                    $this->get('new', $id);
                }
                break;
            case 'update' :
                if ($this->validateActivity() === true) {
                    if ($this->updateActivity($id) === true) {
                        header("Location: /activities/shouts");
                    }
                } else {
                    $this->get('update', $id);
                }
                break;
            case 'delete' :
                if (isset($env->post('activity')['submit'])) {
                    if ($env->post('activity')['submit'] === 'delete') {
                        if ($this->deleteActivity($id) === true) {
                            header("Location: /activities/shouts");
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities/shouts");
                    }
                }
                break;
        }
    }
    
    function getActivity($id) {
        $db = db::getInstance();
        $sql = "SELECT activity_shouts.comments_activated as comments_activated, activity_shouts.content AS content, activities.userid AS userid
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
    
    function getActivityView($act = NULL, $compact = NULL) {
        $act = $this->getActivityById($act);

        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/list_all_activities.php'));

        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
        if (isset($act->create_time)) {
            $subView->addContent('{##activity_published##}', $act->create_time);
        }
        if (isset($act->type_description)) {
            $subView->addContent('{##activity_type##}',  $act->type_description);
        }

        $activity_event = $this->getActivity($act->id);
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

        $subView->replaceTags();
        
        $view->addContent('{##activity_loop##}',  $subView);
        $view->replaceTags();

        return $view;
    }
    
    function getNewActivityForm() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        if ($env->post('activity') === FALSE) {
            $comments_checked = 'checked="checked"';
        } else {
            if (!empty($env->post('activity')['comments']) AND is_string($env->post('activity')['comments']) === TRUE) {
                $comments_checked = 'checked="checked"';
                
            } else {
                $comments_checked = '';
            }
        }
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/shout/new_activity_shout_form.php'), array(
            '{##form_action##}' => '/activity/shout/new',
            '{##activity_content##}' => $env->post('activity')['content'],
            '{##activity_content_validation##}' => $msg->fetch('activity_shout_content_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
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
        
        $comments_checked = (isset($env->post('activity')['comments'])) ? $env->post('activity')['comments'] : $act->comments_activated;
        $comments_checked = ($comments_checked == '1') ? 'checked="' . $comments_checked . '"' : '';

        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/shout/update_activity_shout_form.php'), array(
            '{##form_action##}' => '/activity/shout/update/' . $id,
            '{##activity_content##}' => $content,
            '{##activity_content_validation##}' => $msg->fetch('activity_shout_content_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
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
        $view->setTmpl($view->loadFile('/views/activity/delete_activity_form.php'), array(
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
        $this->save($type=1); // 1=shout

        // save 'shout' specific data
        $activity_id = $db->insert_id;
        $content = $env->post('activity')['content'];
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';

        $sql = "INSERT INTO activity_shouts (activity_id, content, comments_activated) VALUES ('$activity_id', '$content', '$allow_comments');";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
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
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        
        $sql = "UPDATE activity_shouts SET
                        content = '$content',
                        comments_activated = '$allow_comments'
                    WHERE activity_id = '$id';";
        
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
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
            $env->clearPost('activity');
            return true;
        }
        return false;
    }
}
$activity_shout = new Activity_Shout();
$activity_shout->initEnv();
