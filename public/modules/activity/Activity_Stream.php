<?php

class Activity_Stream extends Activity {
    // start controller (i guess)
    function initEnv() {
        Toro::addRoute(["/activities" => "Activity_Stream"]);
        Toro::addRoute(["/activities/:number" => "Activity_Stream"]);
        Toro::addRoute(["/activities/:string" => "Activity_Stream"]);
        Toro::addRoute(["/activities/:string/:number" => "Activity_Stream"]);
    }
    
    function get($alpha = 0, $offset = 0) {
        $env = Env::getInstance();
        $env->clearPost('activity');

        if (is_numeric($alpha)) {
            $page = Page::getInstance();
            $page->setContent('{##main##}', $this->activityMenu('activity'));
            $page->addContent('{##main##}', $this->getAllActivitiesView());
            $page->addContent('{##main##}', $this->setPagination($offset, "/activities/")->paginationView());
        } elseif (is_string($alpha)) {
            $page = Page::getInstance();
            $page->setContent('{##main##}', $this->activityMenu($alpha));
            $type_id = $this->getActivityTypeIDByName($alpha);
            $page->addContent('{##main##}', $this->getAllActivitiesView($type_id));
            $page->addContent('{##main##}', $this->setPagination($offset, "/activities/$alpha/")->paginationView());
        }
        return false;
    }
    // end controller    
    // start model (i suppose)
    function getActivities($interval = NULL, $type = NULL) {
        $db = db::getInstance();
        
        $interval_sql = (is_numeric($interval)) ? "HAVING create_time >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)" : "";
        $type_sql = (is_numeric($type)) ? " AND activities.type = $type" : "";
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $offset_sql = (is_numeric($offset) AND $offset != 0)
            ? "LIMIT $limit OFFSET $offset"
            : "LIMIT $limit";
        $sql = "SELECT
                    activities.id,
                    activities.userid,
                    activities.create_time AS timestamp,
                    activities.comments_enabled AS comments_enabled,
                    activities.deleted as deleted,
                    activities.type AS type,
                    activity_types.name AS type_name,
                    activity_types.description AS type_description,
                    from_unixtime(activities.create_time) AS create_time,
                    (SELECT concat(ae.date, ' ', ae.time) AS timestamp
                        FROM activity_events ae
                        WHERE ae.activity_id = activities.id
                        HAVING DATE_ADD(timestamp,INTERVAL 2 HOUR) >= NOW() AND timestamp <= DATE_ADD(NOW(),INTERVAL 48 HOUR)
                    ) AS event_date,
                    DAY (from_unixtime(activities.create_time)) as event_day
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE activities.deleted = 0
                    $type_sql
                    $interval_sql
                    ORDER BY event_date IS NULL, event_date ASC, activities.create_time DESC
                    $offset_sql
                ";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activities[] = $result_row;
            }
            return $activities;
        }
        return false;
    }

    function getActivityCountByType($type = 0) {
        $db = db::getInstance();
        $sql = "SELECT
                    (SELECT
                        count(*) AS count
                        FROM activities
                        WHERE
                            type = $type
                        AND
                            deleted = 0
                    ) AS count_all,
                    count(*) AS count
                    FROM activities
                    WHERE
                        deleted = 0 AND type = $type
                    AND
                        from_unixtime(create_time) >= DATE_SUB(CURDATE(), INTERVAL 10 DAY);";
        $query = $db->query($sql);
        
        if ($query !== false AND $query->num_rows == 1) {
            $count = $query->fetch_object();
            return $count;
        }
        return false;
    }
    
    function commentsEnabled($activity_id) {
        $db = db::getInstance();
        $sql = "SELECT comments_enabled FROM activities WHERE id = '$activity_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $result_row = $query->fetch_object();
            $result = ($result_row->comments_enabled == '1') ? true : false;
            return $result;
        }
        return false;
    }
    // end model
    // start view (i'd say)
    /*
     * being a patchwork funtion at the moment, a lot of stuff is jerry-rigged here
     * to-do: make this template based, make it human readable again ^^
     */   
    function getAllActivitiesView($type = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        
        $activities = ($type === NULL) ? $this->getActivities(10) : $this->getActivities(NULL, $type);
        if (false !== $activities) {
            $d_var = getdate($activities[0]->timestamp);
            $activity_loop = '';
            if ($activities[0]->event_date == NULL) {
                $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time datetime="'.date('c', $activities[0]->timestamp).'">' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
            }
            $activity_loop .= '<ul class="day_wrapper">';
            $activity_loop .= '<li><ul class="activity_wrapper ' . $activities[0]->type_name . '">';
            $last_type = $activities[0]->type;
            $last_day = $activities[0]->event_day;
            foreach ($activities as $act) {
                if ($last_day != $act->event_day) {
                    $activity_loop .= '</ul></li></ul>';
                    $d_var = getdate($act->timestamp);
                    if ($act->event_date == NULL) {
                        $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time datetime="'.date('c', $act->timestamp).'">' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
                    }
                    $activity_loop .= '<ul class="day_wrapper">';
                    $activity_loop .= '<li><ul class="activity_wrapper ' . $act->type_name . ' ' . $act->id . '">';
                    $last_day = $act->event_day;
                }

                if ($last_type != $act->type) {
                    $activity_loop .= '</ul>';
                    $activity_loop .= '<ul class="activity_wrapper ' . $act->type_name . '">';
                    $last_type = $act->type;
                }

                $subView = $this->getActivityView($act->id, $compact = TRUE);
                $activity_loop .= '<li>' . $subView . '</li>';

            }
            $activity_loop .= '</ul></li>';
            $activity_loop .= '</ul>';
            $view->addContent('{##data##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }
    // end view
}
$init_env = new Activity_Stream();
$init_env->initEnv();
unset($init_env);
