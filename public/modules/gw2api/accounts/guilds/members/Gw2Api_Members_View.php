<?php

class Gw2Api_Members_View {
    public static function getImportMembersForm(Gw2Api_Guilds_Model $guild, $target_url = null) {
        $id = $guild->getId();
        return View::createPrettyButtonForm("/gw2api/members/import/$id", $target_url, "import Guild-Members");
    }    
}
