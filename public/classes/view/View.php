<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of View
 *
 * @author ecv
 */
class View {

    var $src;
    var $tmpl;
    var $tags;
    
    var $areas;
    
    var $content = [];

    function setTmpl() {
        if (is_array(func_get_arg(0))) {
            $this->src = implode('', func_get_arg(0));
        } else {
            $this->src = func_get_arg(0);
        }
        $this->tmpl = $this->src;
        if (func_num_args() > 1) {
            $this->content = func_get_arg(1);
        }
       
        $this->getTemplateTags();
    }
    
    function addContent($key, $content) {
        if (!empty($this->content[$key])) {
            $this->content[$key] .= $content;
        } else {
            $this->content[$key] = $content;
        }
    }

    function setContent($key, $content) {
        $this->content[$key] = $content;
    }
    
    function getSection($content, $start, $end) {
        $r = explode($start, $content);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }


    function getTemplateTags() {
        preg_match_all("|{##.*?##}|", $this->tmpl, $this->tags['open']);
        preg_match_all("|{/##.*?##}|", $this->tmpl, $this->tags['closed']);

        if (is_array($this->tags['closed'][0])) {
            foreach ($this->tags['closed'][0] as $tag) {
                $this->areas[str_replace('/', '', $tag)] = $this->getSection($this->src, str_replace('/', '', $tag), $tag);
            }
        }
    }

    function replaceTags() {
        if (count($this->tags['open'][0]) >= 1) {
            foreach ($this->tags['open'][0] as $index => $tag) {
                if (in_array(str_replace("{##", "{/##", $tag), $this->tags['closed'][0])) {
                    // Starttag hat ein passendes Endtag, also eine Fläche
                    $regex = "|$tag(.*?)" . str_replace("{##", "{/##", $tag) . "|is";
                    $this->tmpl = preg_replace($regex, (isset($this->content[$tag]) ? $this->content[$tag] : '') , $this->tmpl);
                } else {
                    // Kein Endtag, also ein Platzhalter
                    if (isset($this->content[$tag])) {
                        if (is_array($this->content[$tag])) {
                            foreach ($this->content[$tag] as $value) {
                                $content .= $value;
                            }
                        } else {
                            $content = $this->content[$tag];
                        }
                        $this->tmpl = preg_replace("|" . $tag . "|", $content, $this->tmpl);
                    }
                }
            }
        }
        /**
         * Alle übrigen Tags löschen
         * */
        $this->tmpl = preg_replace("~\{##(\w+)##\}~", "", $this->tmpl);
        $this->tmpl = preg_replace("~\{\##(\w+)##\}~", "", $this->tmpl);
        //$this->tmpl = preg_replace("|{##.*##}|", "", $this->tmpl, 1);
        //$this->tmpl = preg_replace("|{/##.*##}|", "", $this->tmpl);
    }
    
    function getSubTemplate($tag) {
        return $this->areas[$tag];
    }
    
    function show() {
        $this->tmpl = preg_replace('/^\h*\v+/m', '', $this->tmpl);
        return $this->tmpl;
    }
    
    function __toString() {
        return $this->show();
    }

}
