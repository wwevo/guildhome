<?php

class Activity_Event_Tags_Model {

    function __construct() {
        $_SESSION['dbconfig']['Activity_Event_Tags_Model'] = $this;
    }

    function saveTags($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $allow_tags = isset($env->post('activity')['tags']) ? '1' : '0';

        $sql = "UPDATE activity_events SET
                tags_activated = '$allow_tags'
            WHERE activity_id = '$activity_id';";
        $query = $db->query($sql);

        if ($db->query($sql)) {
            $msg = Msg::getInstance();
            $msg->add('activity_event_content_saved', 'Tags updated!');
            return true;
        }
        return false;
    }

    protected function createDatabaseTablesByType($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropTagsTables = "DROP TABLE IF EXISTS activity_events_tag_event_mapping, activity_events_tags";
            $db->query($sqlDropTagsTables);
        }
        $activity_events_tags = "CREATE TABLE `activity_events_tags` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(16) NOT NULL,
            `description` VARCHAR(256) NOT NULL,
            `user_id` INT(11) UNSIGNED NOT NULL,
            `creation_date` TIMESTAMP NOT NULL
        );";
        if (false === ($error = $db->query($activity_events_tags))) {
            return false;
        }            
        $activity_events_tag_event_mapping = "CREATE TABLE `activity_events_tag_event_mapping` (
            `tag_id` INT(11) NOT NULL,
            `event_id` INT(11) NOT NULL,
            PRIMARY KEY (`tag_id`, `event_id`)
        );";
        return $db->query($activity_events_tag_event_mapping);
    }
    
}
