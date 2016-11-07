<?php

class Activity_Event_Widgets {
    function getSignupsByUserId($user_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE user_id = '$user_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $activity_event = new Activity_Event();
            while ($result_row = $query->fetch_object()) {
                $signups[] = $activity_event->getActivity($result_row->event_id);
            }
            return $signups;
        }
        return false;
    }

    function getUpcomingActivities() {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events ae LEFT JOIN activities a ON ae.activity_id = a.id WHERE a.deleted = 0 AND date >= DATE(NOW()) ORDER BY date;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity_event = new Activity_Event();
            while ($result_row = $query->fetch_object()) {
                $signups[] = $activity_event->getActivity($result_row->activity_id);
            }

            return $signups;
        }
        return false;
    }
    
    function getSignupsByUserIdView($user_id) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/widget/activity_signup_schedule_view.php'));
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##widget_title##}'));
        $subView->addContent('{##widget_title_text##}', 'Signup Schedule/History');
        $subView->replaceTags();
        $view->addContent('{##widget_title##}', $subView);

        $signups = $this->getSignupsByUserId($user_id);
        if (false !== $signups && is_array($signups)) {
            $signup_loop = NULL;
            foreach ($signups as $signup) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##signup_loop##}'));
                $subView->addContent('{##activity_title##}', $signup->title);
                $subView->addContent('{##activity_details_link##}', View::linkFab('/activity/event/details/' . $signup->activity_id, 'Event details'));
                $subView->addContent('{##activity_event_date##}', $signup->date);
                $subView->addContent('{##activity_event_time##}', $signup->time);
                $subView->replaceTags();
                $signup_loop .= $subView;
            }
            $view->addContent('{##signup_loop##}', $signup_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
    function getUpcomingActivitiesView() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/widget/scheduled_activities.php'));
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##widget_title##}'));
        $subView->addContent('{##widget_title_text##}', 'Upcoming Events');
        $subView->replaceTags();
        $view->addContent('{##widget_title##}', $subView);

        $act = $this->getUpcomingActivities();
        if (false !== $act && is_array($act)) {
            $activity_loop = NULL;
            foreach ($act as $activity) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##event_loop##}'));
                $subView->addContent('{##activity_title##}', $activity->title);
                $subView->addContent('{##activity_details_link##}', '/activity/event/details/' . $activity->activity_id);
                $subView->addContent('{##activity_details_link_text##}', 'Event details');
                $date = new DateTime($activity->date . ' ' . $activity->time);
                $subView->addContent('{##activity_event_date##}', $date->format('Y-m-d'));
                $subView->addContent('{##activity_event_time##}', $date->format('H:i'));
                $subView->addContent('{##activity_event_datetime##}', $date->format('Y-m-d H:i'));

                $subView->replaceTags();
                $activity_loop .= $subView;
            }
            $view->addContent('{##event_loop##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
}
