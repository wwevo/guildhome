<?php

class Database_View {

    function getSelectTablesFormView() {
//        ob_start();
//        echo "<pre>";
//        var_dump($_SESSION['dbconfig']);
//        echo "</pre>";
//        $content = ob_get_contents();
//        ob_end_clean();
        
        $registered_models = $_SESSION['dbconfig'];
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        if (is_array($registered_models)) {
            $view->addContent('{##data##}', '<select name="selected_models[]" size="' . count($registered_models) . '" multiple="multiple">');
            foreach ($registered_models as $class_name => $modelObject) {
                $view->addContent('{##data##}', '<option>' . $class_name . '</option>');
            }
            $view->addContent('{##data##}', '</select>');
        } else {
            $view->addContent('{##data##}', 'No registered models found!');
        }
        $view->replaceTags();
        return $view;
    }
}
