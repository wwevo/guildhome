<?php

class Activity_Event_Tags_View {

    function getActivityTagsView($event_id) {
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivityByID($event_id);

        $tags_checked = $act->tags_activated;
        if ($tags_checked == '1') {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/activity/event/tags/activity_event_tags_view.php'), array(
                '{##tags##}' => $tags_checked,
            ));

            $view->replaceTags();
            return $view;
        }
        return false;
    }
    
    function getTagsFormView($id, $target_url = '') {
        $msg = Msg::getInstance();
        $env = Env::getInstance();
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/tags/activity_event_tags_form.php'));
        
        $view->addContent('{##form_action##}', '/activity/event/tags/update/' . $id);
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivityByID($id);
        $signups_checked = (!empty($env->post('activity')['tags'])) ? $env->post('activity')['tags'] : $act->tags_activated;

        $view->addContent('{##target_url##}',$target_url);
        $view->addContent('{##activity_tags_checked##}', ($signups_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##submit_text##}', 'Update');
        $view->replaceTags();
        
        return $view;        
    }

    function tagInfuser($event_id, $compact = NULL) {
        $activity_event = new Activity_Event();
        $event = $activity_event->getActivityById($event_id);
        $tags = '';
        if ($event->tags_activated == '1') {
            $tags = "Tags active";
            $tag_collection['{##sub_module_css##}'] = ' tags_enabled';
        } else {
            return false;
        }
        // To-Do: tag_collection should be part of the View Class
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/tags/activity_event_tags_view.php'));
        if (is_null($compact)) {
            $view->addContent('{##activity_tags##}', $tags);
            $view->replaceTags();
            $tag_collection['{##tags_details##}'] = $view;
            return $tag_collection;
        }
        return false;
    }
}
