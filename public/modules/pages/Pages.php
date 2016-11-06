<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Home
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Pages {

    function initEnv() {
        Toro::addRoute(["/" => 'Pages']);
        Toro::addRoute(["/:alpha" => 'Pages']);
    }

    private $registered_pages = ['home', 'about', '7dtd', 'imprint', 'impressum', 'privacy', 'datenschutz'];

    function get($slug = 'home') {
        $page = Page::getInstance();


        if (in_array($slug, $this->registered_pages)) {
            $page->setContent('{##main##}', $this->getPageBySlug($slug));
        } else {
            header("Location: /home");
            exit;
        }
    }

    function getPageBySlug($slug) {
        $db = DB::getInstance();
        $sql = "SELECT * FROM pages_slugs ps LEFT JOIN pages p ON ps.pages_id = p.id WHERE ps.slug = '$slug'";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result->content;
        }
        return false;
    }

}

$pages = new Pages();
$pages->initEnv();
