<?php
/**
 * Widget classes are a bit different, they should not perfomr any extensive
 * tasks, but should concentrate on displaying data for now.
 * We'll throw Model/View/Controller into one file
 */
class Gw2Api_Members_Widgets {

    function getRankUsageFromRoster() {
        $db = db::getInstance();
        $sql = "SELECT guild_rank, COUNT(*) as rank_count FROM gw2api_members GROUP BY guild_rank ORDER BY FIELD(guild_rank, 'Member', 'Chieftain', 'Officer', 'Leader') DESC, rank_count;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $rank_usage[] = $result_row;
            }
            return $rank_usage;
        }
        return false;
    }

    function getRankUsageFromRosterView() {
        $ranks = $this->getRankUsageFromRoster();
        if (FALSE !== $ranks) {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/rank_usage_view.php'));
            $all_ranks = null;
            if (is_array($ranks)) {
                foreach ($ranks as $rank) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##ranks##}'));
                    $option->addContent('{##rank_description##}', $rank->guild_rank);
                    $option->addContent('{##rank_count##}', $rank->rank_count);
                    $option->replaceTags();
                    $all_ranks .= $option;
                }
            }
            if (is_null($all_ranks)) {
                $view->addContent('{##ranks##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##ranks##}', $all_ranks);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }

}
