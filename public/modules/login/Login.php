<?php

/**
 * Class login
 * handles the user's login and logout process
 */
class Login {
    /**
     * @var object The database connection
     */

    function initEnv() {
        Toro::addRoute(["/login" => 'Login']);
        Toro::addRoute(["/logout" => 'Login']);
    }
    
    public function get() {
        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Login</h2>');
        $page->addContent('{##main##}', $this->getCombinedLoginView());
    }
    
    public function post() {
        $env = Env::getInstance();
        if (isset($env->post('login')['submit'])) {
            if ($this->dologinWithPostData() === true) {
                header("Location: /activities");
            } else {
                header("Location: /login");
            }
        }
        
        if (isset($env->post('logout')['submit'])) {
            $this->doLogout();
            header("Location: /");
        }
    }

    private function dologinWithPostData() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();

        $error = 0;
        if (empty($env->post('login')['username'])) {
            $msg->add('login_username_validation', 'Please type in your username');
            $error = 1;
        }

        if (empty($env->post('login')['password'])) {
            $msg->add('login_password_validation', 'Please provide a password. Preferably yours :)');
            $error = 1;
        }

        if ($error == 1) {
            return false;
        }

        $db = db::getInstance();
        $username = $db->real_escape_string($env->post('login')['username']);
        $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, users.password_hash, user_ranks.description AS rank_description
                    FROM users
                    INNER JOIN user_ranks
                    ON users.rank = user_ranks.id
                    WHERE users.username = '" . $username . "' OR users.email = '" . $username . "';"; // login via email address or username
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {
            // user or email exists
            $result_row = $result->fetch_object();
            if (password_verify($env->post('login')['password'], $result_row->password_hash)) {
                $this->setSessionData($result_row);
                return true;
            } else {
                $msg->add('login_password_validation', 'Password is wrong. Concentrate!!');
            }
        } else {
            $msg->add('login_username_validation', 'Unknown Username/email. Think Hard!!');
        }
        return false;
    }
    
    private function setSessionData($result_row) {
        $_SESSION['evo']['user_id'] = $result_row->id;
        $_SESSION['evo']['username'] = $result_row->username;
        $_SESSION['evo']['email'] = $result_row->email;
        switch ($result_row->rank) {
            default:
            case '2' :
                $_SESSION['evo']['user_is_admin'] = null;
                $_SESSION['evo']['user_is_operator'] = null;
                break;
            case '1' :
                $_SESSION['evo']['user_is_operator'] = true;
                break;
            case '0' :
                $_SESSION['evo']['user_is_admin'] = true;
                break;
        }

        $_SESSION['evo']['user_login_status'] = 1;
    }
    
    public function doLogout() {
        if ($this->isLoggedIn()) {
            // delete the session of the user
            $_SESSION['evo'] = null;
        }
    }

    public function isLoggedIn() {
        if (isset($_SESSION['evo']['user_login_status']) AND $_SESSION['evo']['user_login_status'] == 1) {
            return true;
        }
        return false;
    }

    public function currentUserID() {
        if ($this->isLoggedIn()) {
            return $_SESSION['evo']['user_id'];
        }
        return false;
    }
    
    public function currentUsername() {
        if ($this->isLoggedIn()) {
            return $_SESSION['evo']['username'];
        }
        return false;
    }
    
    public function isOperator() {
        if (isset($_SESSION['evo']['user_is_operator']) AND $_SESSION['evo']['user_is_operator'] == 1
            OR $this->isAdmin()) {
            return true;
        }
        return false;
    }
    
    public function isAdmin() {
        if (isset($_SESSION['evo']['user_is_admin']) AND $_SESSION['evo']['user_is_admin'] == 1) {
            return true;
        }
        return false;
    }
    
    /*
     * Views -> These have to be public and may not echo or print ANY data.
     * Use the Msg class to display debug stuff if you have to.
     * Functions may only return 'text' data or 'false'
     */
    public function getCombinedLoginView() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        if ($this->isLoggedIn() == true) {
            $view->setTmpl(file('views/core/login/logout_form.php'), array(
                '{##form_action##}' => '/logout',
                '{##logout_link##}' => '/logout',
                '{##logout_link_text##}' => 'Logout ' . $_SESSION['evo']['username'],
            ));
        } else {
            $view->setTmpl(file('views/core/login/login_form.php'), array(
                '{##form_action##}' => '/login',
                '{##login_username##}' => $env->post('login')['username'],
                '{##login_username_text##}' => 'Username',
                '{##login_username_validation##}' => $msg->fetch('login_username_validation'),
                '{##login_password##}' => '',
                '{##login_password_text##}' => 'Password',
                '{##login_password_validation##}' => $msg->fetch('login_password_validation'),
                '{##login_submit_text##}' => 'Log in',
                '{##register_link##}' => '/register/',
                '{##register_link_text##}' => 'register new user',
            ));
        }
        $view->replaceTags();
        return $view;
    }
    
}
$login = new Login();
$login->initEnv();
