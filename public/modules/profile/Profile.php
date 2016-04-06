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
        $page->setContent('{##main##}', '<h2>Registered Members</h2>');
        
        if (empty($slug)) {
            $page->addContent('{##main##}', $this->getUsersView());
        } else {
            $db = db::getInstance();
            $user = $this->getUsers($db->real_escape_string($slug))[0];
            if (is_object($user)) {
                if ($user->id == $login->currentUserID()) { // it's-a-me!
                    $settings = new Settings();
                    $page->addContent('{##main##}', 
                        $user->rank_description . '<br />' .
                        $user->username . '<br />' .
                        $user->email . '<hr />'
                    );
                    $page->addContent('{##main##}', '<p>use any image url</p>');
                    $page->addContent('{##main##}', $settings->getUpdateSettingForm('avatar'));
                    $page->addContent('{##main##}', '<p>just copy and paste from your guild wars account page. Only account and guilds are required, characters would be nice.</p>');
                    $page->addContent('{##main##}', $settings->getUpdateSettingForm('api_key'));
                } else {
                    $page->addContent('{##main##}',
                        $user->rank_description . '<br />' .
                        $user->username
                    );
                }
            } else {
                $page->addContent('{##main##}', 'unknown user');
            }
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
