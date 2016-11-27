<?php
/**
 * Widget classes are a bit different, they should not perfomr any extensive
 * tasks, but should concentrate on displaying data for now.
 * We'll throw Model/View/Controller into one file
 */
class Gw2Api_Characters_Widgets {

    function buildSorter($key) {
       return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
    
    function getNextBirthdays($charactersObject_collection) {
        $msg = Msg::getInstance();
        
        if (isset($charactersObject_collection) AND is_array($charactersObject_collection)) {
            foreach ($charactersObject_collection as $character) {
                $characters_birthdays[] = array(
                    'name' => $character->getName(),
                    'birthday_in' => $character->getBirthdayIn(),
                );
            }
            usort($characters_birthdays, $this->buildSorter('birthday_in'));
            return $characters_birthdays;
        } else {
            return false;
        }
    }
    
    function getNextBirthdaysView($charactersObject_collection) {
        $characters_birthdays = $this->getNextBirthdays($charactersObject_collection);
        $msg = Msg::getInstance();
        if ($characters_birthdays !== false) {
            $characters_birthdays = array_slice($characters_birthdays, 0, 3);
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/widget/next_character_birthdays_view.php'));
            $subView = new View();
            $subView->setTmpl($view->getSubTemplate('{##widget_title##}'));
            $subView->addContent('{##widget_title_text##}', 'Upcoming birthdays');
            $subView->replaceTags();
            $view->addContent('{##widget_title##}', $subView);
            $view->addContent('{##widget_warning##}', $msg->fetch('api_data_outdated'));
            $all_characters = null;
            if (is_array($characters_birthdays)) {
                foreach ($characters_birthdays as $character) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##characters##}'));
                    $option->addContent('{##name##}', $character['name']);
                    $option->addContent('{##birthday_in##}', $character['birthday_in']);
                    $option->replaceTags();
                    $all_characters .= $option;
                }
            }
            if (is_null($all_characters)) {
                $view->addContent('{##characters##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##characters##}', $all_characters);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }
}
