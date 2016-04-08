<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Profile
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Profile {
    
    function initEnv() {
        Toro::addRoute(["/profiles" => 'Profile']);
        Toro::addRoute(["/profile/:alpha" => 'Profile']);
    }
    
    function get($slug = NULL) {
        $login = new Login();
        $page = Page::getInstance();
        
        if (empty($slug)) {
            $page->setContent('{##main##}', '<h2>Registered Members</h2>');
            $page->addContent('{##main##}', $this->getUsersView());
        } else {
            $page->setContent('{##main##}', '<h2>Profile</h2>');
            $db = db::getInstance();
            $user = $this->getUsers($db->real_escape_string($slug))[0];
            
            $view = new View();
            $view->setTmpl(file('views/profile/profile_main.php'));

            if (is_object($user)) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##profile_badge##}'));
                $identity = new Identity();
                $subView->addContent('{##rank##}', $user->rank_description);
                $subView->addContent('{##display_name##}', $user->username);
                $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($user->id));

                if ($user->id == $login->currentUserID()) { // it's-a-me!
                    $subView->addContent('{##email##}', $user->email);

                    $settings = new Settings();
                    $view->addContent('{##main##}', '<p>Desired Displayname</p>');
                    $view->addContent('{##main##}', $settings->getUpdateSettingForm('display_name'));
                    $view->addContent('{##main##}', '<p>use any image url, only direct links will work</p>');
                    $view->addContent('{##main##}', $settings->getUpdateSettingForm('avatar'));
                    $view->addContent('{##main##}', '<p>just copy and paste from your guild wars account page. Only account and guilds are required, characters would be nice.</p>');
                    $view->addContent('{##main##}', $settings->getUpdateSettingForm('api_key'));
                }
                $subView->replaceTags();
                $view->addContent('{##profile_badge##}', $subView);
            } else {
                $view->addContent('{##main##}', 'unknown user');
            }
            $view->replaceTags();
            $page->addContent('{##main##}', $view);
        }

    }

    public function getUsers($user = null) {
        $db = db::getInstance();
        if (is_null($user)) {
            $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, user_ranks.description AS rank_description
                        FROM users
                        INNER JOIN user_ranks
                        ON users.rank = user_ranks.id
                        WHERE users.rank != '0'
                        ORDER BY users.id ASC;";
        } else {
            $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, user_ranks.description AS rank_description
                        FROM users
                        INNER JOIN user_ranks
                        ON users.rank = user_ranks.id
                        WHERE users.id = '" . $user . "'
                        OR users.username = '" . $user . "';";
        }
        
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {
            while ($result_row = $result->fetch_object()) {
                $accounts[] = $result_row;
            }
            return $accounts;
        } else {
            return false;
        }
    }

    public function getUsersView() {
        $view = new View();
        $view->setTmpl(file('views/core/login/all_users_view.php'));

        $all_users = null;
        $users = $this->getUsers();
        if (is_array($users)) {
            foreach ($users as $user) {
                $option = new View();
                $option->setTmpl($view->getSubTemplate('{##users##}'));
                $option->addContent('{##user_id##}', $user->id);
                $option->addContent('{##username##}', $user->username);
                $option->addContent('{##rank##}', $user->rank);
                $option->addContent('{##rank_description##}', $user->rank_description);
                $option->replaceTags();
                $all_users .= $option;
            }
        }
        if (is_null($all_users)) {
            $view->addContent('{##users##}', 'no users so far');
        } else {
            $view->addContent('{##users##}', $all_users);
        }

        $view->replaceTags();
        return $view;
    }

}
$profile = new Profile();
$profile->initEnv();
