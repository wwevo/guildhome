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
        Toro::addRoute(["/login/:alpha" => 'Login']);
        Toro::addRoute(["/login/:alpha/:alpha" => 'Login']);
        Toro::addRoute(["/logout" => 'Login']);
    }
    
    public function get($alpha = '', $token= '') {
        $page = Page::getInstance();
        
        switch ($alpha) {
            default:
                $page->setContent('{##main##}', '<h2>Login</h2>');
                $page->addContent('{##main##}', $this->getCombinedLoginView());
                break;
            case 'change_password' :
                $page->setContent('{##main##}', '<h2>Change Password</h2>');
                $page->addContent('{##main##}', $this->getChangePasswordView());
                break;
            case 'set_password' :
                $page->setContent('{##main##}', '<h2>Set Password</h2>');
                $page->addContent('{##main##}', $this->getSetPasswordView());
                break;
            case 'reset_password' :
                if ($token != '' && strlen($token) == 32) {
                    $db = db::getInstance();
                    $page->setContent('{##main##}', '<h2>Token recieved</h2>');
                    $token_clean = $db->real_escape_string(strip_tags($token, ENT_QUOTES));
                    $token_user_id = $this->checkToken($token);
                    if ($token_user_id !== false) {
                        $this->doLoginById($token_user_id);
                        $this->clearResetToken($token_user_id);
                        header("Location: /login/set_password");
                    }
                } else {
                    $page->setContent('{##main##}', '<h2>Reset Password</h2>');
                    $page->addContent('{##main##}', $this->getResetPasswordView());
                }
                break;
        }
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

        if (isset($env->post('change_password')['submit'])) {
            if ($this->changePassword() === true) {
                $this->doLogout();
                header("Location: /login");
            } else {
                header("Location: /login/change_password");
            }
        }

        if (isset($env->post('set_password')['submit'])) {
            if ($this->setPassword() === true) {
                $this->doLogout();
                header("Location: /login");
            } else {
                header("Location: /login/set_password");
            }
        }

        if (isset($env->post('reset_password')['submit'])) {
            $validate_email = $this->validateEmail();
            if ($validate_email !== false) {
                // spaghetti code deluxe!! needs to be refactored like a broken-down factory ^^
                $token = bin2hex(random_bytes(16));
                $this->storeResetToken($token, $validate_email->id);
                $this->eMailToken($token, $validate_email->email);
            } else {
                header("Location: /login/reset_password");
            }
        }
    }
    
    /**
 * Generate a random string, using a cryptographically secure 
 * pseudorandom number generator (random_int)
 * 
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
    function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
    
    private function storeResetToken($token, $user_id) {
        $db = db::getInstance();
        $this->clearResetToken($user_id);
        $sql = "INSERT INTO reset_token(user_id,token,timestamp,email_sent)
                    VALUES('$user_id', '$token', NOW(), 0);";
        $result = $db->query($sql);
    }
    
    private function clearResetToken($user_id) {
        $db = db::getInstance();
        $sql = "DELETE FROM reset_token WHERE user_id = $user_id";
        $result = $db->query($sql);
        
        // clear out every old token while we are at it :)
        $this->clearOldTokens();
    }

    private function clearOldTokens() {
        $db = db::getInstance();
        $sql = "DELETE FROM reset_token WHERE timestamp < DATE_ADD(NOW(), INTERVAL - 38 MINUTE)";
        $result = $db->query($sql);
    }
    
    private function hasActiveToken($user_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM reset_token WHERE user_id = '" . $this->currentUserID() . "';";
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {
            return true;
        }        
        return false;
    }
    
    private function checkToken($token) {
        $db = db::getInstance();
        $sql = "SELECT * FROM reset_token WHERE token = '$token';";
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {
            $result_row = $result->fetch_object();
            return $result_row->user_id;
        }        
        return false;
    }
    
    private function eMailToken($token, $email) {
        $reset_link = 'http://' . GH_BASEDIR . '/login/reset_password/'. $token; 
        $mail = new PHPMailer;
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = MAILHOST;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = MAILUSER;                 // SMTP username
        $mail->Password = MAILPASS;                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->setFrom('mail@notjustfor.me', 'Support');
        $mail->addAddress($email);     // Add a recipient


        $mail->Subject = 'Password reset for EoL Account';
        $mail->Body    = 'password reset link: ' . $reset_link;
        $mail->AltBody = '';

        if($mail->send()) {
            return true;
        }        
        return false;
    }

    private function validateEmail() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();
        
        $error = 0;
        if (empty($env->post('reset_password')['registered_email'])) {
            $msg->add('registered_email_validation', 'Please type in your email address');
            $error = 1;
        } elseif (!filter_var($env->post('reset_password')['registered_email'], FILTER_VALIDATE_EMAIL)) {
            $msg->add('registered_email_validation', "please use a valid eMail Adress!");
            $error = 1;
        }

        // early getaway, no need to database stuff that ain't valid in the first place
        if ($error == 1) {
            return false;
        }

        $registered_email = $env->post('reset_password')['registered_email'];
        $db = db::getInstance();

        $sql = "SELECT id, email FROM users WHERE email = '$registered_email';";
        $result = $db->query($sql);
        if ($result->num_rows >= 1) {
            $result_row = $result->fetch_object();
            return $result_row;
        } else {
            $msg->add('registered_email_validation', "email address unknown to me! Whoa!");
        }

        return false;
    }
    
    private function setRandomPasswordForUserId($user_id) {
        $db = db::getInstance();
        $password = $this->random_str(8); 
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = '$password_hash' WHERE id = '$user_id';";
        $result = $db->query($sql);
        return $password;
    }

    private function setPassword() {
        if ($this->isLoggedIn() === false) {
            return false;
        }
        
        $env = Env::getInstance();
        $msg = Msg::getInstance();
        $db = db::getInstance();
        $username = $db->real_escape_string($this->currentUsername());
        
        $error = 0;
        if (empty($env->post('set_password')['password_new']) AND empty($env->post('set_password')['password_repeat'])) {
            $msg->add('new_password_validation', "No password. Good plan! NOT!!");
            $msg->add('new_password_repeat_validation', "Hey, this one matches the empty one! That's something, isn't it?");
            $error = 1;
        } elseif ($env->post('set_password')['password_new'] !== $env->post('set_password')['password_repeat']) {
            $msg->add('new_password_repeat_validation', "Variation is nice. you get a richer life and everything. Not with passwords though, make sure that they match ^^");
            $error = 1;
        } elseif (strlen($env->post('set_password')['password_new']) < 6) {
            $msg->add('new_password_validation', "You were asked to provide a password, not an abbreviation of one! Use at least six characters!");
            $error = 1;
        }
        
        if ($error == 1) {
            return false;
        }
        
        $password = $env->post('set_password')['password_new'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = '$password_hash' WHERE username = '$username';";
        $result = $db->query($sql);

        if ($result) {
            $msg->add('set_password_general_validation', "Password for user " . $username . " has been changed.");
            $env->clear_post('set_password');
            return true; // user creation complete
        } else {
            $msg->add('set_password_general_validation', "Something unexpected happened during Database operations. No password has been changed.");
            return false;
        }
        return true;
    }

    
    private function changePassword() {
        if ($this->isLoggedIn() === false) {
            return false;
        }
        
        $env = Env::getInstance();
        $msg = Msg::getInstance();
        $db = db::getInstance();
        $username = $db->real_escape_string($this->currentUsername());
        
        $error = 0;
        if (empty($env->post('change_password')['password_current'])) {
            $msg->add('current_password_validation', 'Please provide a password. Preferably yours :)');
            $error = 1;
        } else {
            // check if password is correct. Needs cleanup, this one ^^
            $sql = "SELECT password_hash FROM users WHERE username = '$username';";
            $result = $db->query($sql);
            if ($result->num_rows >= 1) {
                $result_row = $result->fetch_object();
                if (!password_verify($env->post('change_password')['password_current'], $result_row->password_hash)) {
                    $msg->add('current_password_validation', 'Current password is not correct');
                    $error = 1;
                } else {
                    $msg->add('current_password_validation', 'Password was correct!');
                }
            }
        }

        if (empty($env->post('change_password')['password_new']) AND empty($env->post('change_password')['password_repeat'])) {
            $msg->add('new_password_validation', "No password. Good plan! NOT!!");
            $msg->add('new_password_repeat_validation', "Hey, this one matches the empty one! That's something, isn't it?");
            $error = 1;
        } elseif ($env->post('change_password')['password_new'] !== $env->post('change_password')['password_repeat']) {
            $msg->add('new_password_repeat_validation', "Variation is nice. you get a richer life and everything. Not with passwords though, make sure that they match ^^");
            $error = 1;
        } elseif (strlen($env->post('change_password')['password_new']) < 6) {
            $msg->add('new_password_validation', "You were asked to provide a password, not an abbreviation of one! Use at least six characters!");
            $error = 1;
        }
        
        if ($error == 1) {
            return false;
        }
        
        $password = $env->post('change_password')['password_new'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = '$password_hash' WHERE username = '$username';";
        $result = $db->query($sql);

        if ($result) {
            $msg->add('change_password_general_validation', "Password for user " . $username . " has been changed.");
            $env->clear_post('change_password');
            return true; // user creation complete
        } else {
            $msg->add('change_password_general_validation', "Something unexpected happened during Database operations. No password has been changed.");
            return false;
        }
        return true;
    }

    private function doLoginById($id) {
        $db = db::getInstance();
        $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, users.password_hash, user_ranks.description AS rank_description
                    FROM users
                    INNER JOIN user_ranks
                    ON users.rank = user_ranks.id
                    WHERE users.id = '$id';";
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {

            $result_row = $result->fetch_object();
            $this->setSessionData($result_row);
            return true;
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
            $view->setTmpl(file('themes/' . constant('theme') . '/views/core/login/logout_form.php'), array(
                '{##form_action##}' => '/logout',
                '{##logout_link##}' => '/logout',
                '{##logout_link_text##}' => 'Logout ' . $_SESSION['evo']['username'],
            ));
        } else {
            $view->setTmpl(file('themes/' . constant('theme') . '/views/core/login/login_form.php'), array(
                '{##form_action##}' => '/login',
                '{##login_username##}' => $env->post('login')['username'],
                '{##login_username_text##}' => 'Username',
                '{##login_username_validation##}' => $msg->fetch('login_username_validation'),
                '{##login_password##}' => '',
                '{##login_password_text##}' => 'Password',
                '{##login_password_validation##}' => $msg->fetch('login_password_validation'),
                '{##login_submit_text##}' => 'Log in',
                '{##login_forgot_text##}' => 'Forgot Password',
                '{##register_link##}' => '/register/',
                '{##register_link_text##}' => 'register new user',
                '{##reset_password_link##}' => '/login/reset_password',
                '{##reset_password_link_text##}' => 'lost your password?',
            ));
        }
        $view->replaceTags();
        return $view;
    }
    
    public function getChangePasswordView() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        if ($this->isLoggedIn() == true) {
            $view->setTmpl(file('themes/' . constant('theme') . '/views/core/login/change_password_form.php'), array(
                '{##form_action##}' => '/login/change_password',
                '{##current_password##}' => '',
                '{##current_password_text##}' => 'Current password',
                '{##current_password_validation##}' => $msg->fetch('current_password_validation'),
                '{##new_password##}' => '',
                '{##new_password_text##}' => 'New password',
                '{##new_password_validation##}' => $msg->fetch('new_password_validation'),
                '{##new_password_repeat##}' => '',
                '{##new_password_repeat_text##}' => 'Repeat new password',
                '{##new_password_repeat_validation##}' => $msg->fetch('new_password_repeat_validation'),
                '{##change_password_submit_text##}' => 'Change password',
                '{##change_password_general_information##}', $msg->fetch('change_password_general_information'),
            ));
        }
        $view->replaceTags();
        return $view;
    }

    public function getSetPasswordView() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        if ($this->isLoggedIn() === true AND $this->hasActiveToken($this->currentUserID()) === true) {
           if ($this->isLoggedIn() == true) {
                $view->setTmpl(file('themes/' . constant('theme') . '/views/core/login/set_password_form.php'), array(
                    '{##form_action##}' => '/login/set_password',
                    '{##new_password##}' => '',
                    '{##new_password_text##}' => 'New password',
                    '{##new_password_validation##}' => $msg->fetch('new_password_validation'),
                    '{##new_password_repeat##}' => '',
                    '{##new_password_repeat_text##}' => 'Repeat new password',
                    '{##new_password_repeat_validation##}' => $msg->fetch('new_password_repeat_validation'),
                    '{##set_password_submit_text##}' => 'Change password',
                ));
            }
        }
        $view->replaceTags();
        return $view;
    }

    public function getResetPasswordView() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        $view->setTmpl(file('themes/' . constant('theme') . '/views/core/login/reset_password_form.php'), array(
            '{##form_action##}' => '/login/reset_password',
            '{##registered_email##}' => $env->post('reset_password')['registered_email'],
            '{##registered_email_description##}' => 'Please provide the email address that you have registered your account with.',
            '{##registered_email_text##}' => 'email',
            '{##registered_email_validation##}' => $msg->fetch('registered_email_validation'),
            '{##reset_password_submit_text##}' => 'Reset password',
        ));
        $view->replaceTags();
        return $view;
    }
    
}
$login = new Login();
$login->initEnv();
