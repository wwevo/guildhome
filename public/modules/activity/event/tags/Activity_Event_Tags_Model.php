<?php

class Activity_Event_Tags_Model {

    private $id = null;
    private $name = null;
    private $description = null;
    private $user_id = null;
    private $creation_date = null;
    
    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    function getUserId() {
        return $this->user_id;
    }

    function getCreationDate() {
        return $this->creation_date;
    }

    function setId($id) {
        $this->id = $id;
        return $this;
    }

    function setName($name) {
        $this->name = $name;
        return $this;
    }

    function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    function setUserId($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    function setCreationDate($creation_date) {
        $this->creation_date = $creation_date;
        return $this;
    }

    function __construct() {
        $_SESSION['dbconfig']['Activity_Event_Tags_Model'] = $this;
    }

    static function getTagObjectById($id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_tags WHERE id = $id;";
        if (false !== ($query = $db->query($sql)) AND $query->num_rows == 1) {
            $tag_row = $query->fetch_object();
            $tagObject = new self;
            $tagObject->setName($tag_row->name)->setDescription($tag_row->description)->setUserId($tag_row->user_id)->setId($tag_row->id)->setCreationDate($tag_row->creation_date);
            return $tagObject;
        }
    }
    
    static function getAvailableTagObjects() {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_tags;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($tag_row = $query->fetch_object()) {
                $tagObject_collection[] = self::getTagObjectById($tag_row->id);
            }
            return $tagObject_collection;
        }
        return false;
    }

    static function getTagObjectByName($name) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_tags WHERE name = '$name';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            $tag_row = $query->fetch_object();
            return self::getTagObjectById($tag_row->id);
        }
        return false;
    }
    
    function toggleActivation($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $allow_tags = isset($env->post('activity')['tags']['enabled']) ? '1' : '0';
        $sql = "UPDATE activity_events SET tags_activated = '$allow_tags' WHERE activity_id = $activity_id;";
        echo $sql;
        if (false === $db->query($sql)) {
            Msg::add('activity_event_tags_error', "Updating tag-usage to '$allow_tags' failed!");
            return false;
        }
        return true;
    }
    
    function save($activity_id) {
        $db = db::getInstance();

        $name = $this->getName();
        $description = $this->getDescription();
        $user_id = $this->getUserId();
        $creation_date = $this->getCreationDate();

        if (false === self::getTagObjectByName($name)) {
            $sql = "INSERT INTO activity_events_tags (`name`, description, user_id, creation_date) 
                        VALUES ('$name', '$description', $user_id, '$creation_date');";

            if ($db->query($sql)) {
                Msg::add('activity_event_tags_success', 'Tags updated!');
                return true;
            }
        } else {
            Msg::add('activity_event_tags_error', 'A Tag with that name already exists!');
        }
        return false;
    }

    public function createDatabaseTables($overwriteIfExists) {
        $db = db::getInstance();
        if ($overwriteIfExists) {
            $sqlDropTagsTables = "DROP TABLE IF EXISTS activity_events_tag_event_mapping, activity_events_tags";
            $db->query($sqlDropTagsTables);
        }
        $activity_events_tags = "CREATE TABLE `activity_events_tags` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(16) NOT NULL,
            `description` VARCHAR(256),
            `user_id` INT(11) UNSIGNED NOT NULL,
            `creation_date` INT(15) NOT NULL
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
