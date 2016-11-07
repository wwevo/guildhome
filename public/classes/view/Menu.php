<?php

class Menu {
    function getMenu($name = 'site') {
        $login = new Login();
        switch ($name) {
            default: 
            case 'site':
                    $menu  = '<ul class="site-menu">';
                    $menu .= '  <li><a href="/">Home</a></li>';
                    $menu .= '  <li><a href="/activities">Activities</a></li>';
                    $menu .= '  <li><a href="/profiles">Members</a></li>';
                    $menu .= '  <li><a href="/pages/view/about">About EoL</a></li>';
                    $menu .= '</ul>';
                break;
            case 'user':
                    $menu  = '<ul class="user-menu">';
                    if ($login->isLoggedIn()) {
                        $menu .= '  <li><a href="/profile/' . $login->currentUsername() . '">Profile</a>';
                        $menu .= '  <ul>';
                        $menu .= '      <li><a href="/profile/' . $login->currentUsername() . '/settings">Settings</a></li>';
                        $gw2api = new gw2api();
                        if ($gw2api->hasApiData('characters')) {
                            $menu .= '      <li><a href="/profile/' . $login->currentUsername() . '/characters">Characters</a></li>';
                        }
                        $menu .= '  </ul>';
                        $menu .= '  </li>';
                        $menu .= '  <li><a href="/logout">Logout</a></li>';
                    } else {
                        $menu .= '  <li><a href="/register">Register</a></li>';
                        $menu .= '  <li><a href="/login">Login</a></li>';
                    }
                    $menu .= '</ul>';
                break;
            case 'operator':
                    if ($login->isLoggedIn()) {
                        $menu  = '<hr />';
                        $menu .= '<ul class="operator-menu">';
                        $menu .= '  <li><a href="/gw2api">gw2api (test)</a></li>';
                        $menu .= '</ul>';
                    } else {
                        $menu = '';
                    }
                break;
        }
        return $menu;
    }
}
