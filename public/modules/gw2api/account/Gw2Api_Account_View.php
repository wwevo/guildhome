<?php

class Gw2Api_Account_View {
    
    /**
     * Displays a prettyButton which will start the import procedure on account
     * data. Requires a 'Gw2Api_Keys_Model' Object
     * 
     * @param   Gw2Api_Keys_Model   $gw2_api_key    accepts 'Gw2Api_Keys_Model' Objects
     * @param   type                $target_url     URL to redirect to after form submit
     * @return type
     */
    public static function getImportAccountForm(Gw2Api_Keys_Model $gw2_api_key, $target_url = null) {
        $gw2_api_key_id = $gw2_api_key->getId();
        if ($target_url !== null) {
            return View::createPrettyButtonForm("/gw2api/account/import/$gw2_api_key_id", $target_url, "import Account-Data");
        }
        return View::createPrettyButtonForm("/gw2api/account/import/$gw2_api_key_id", null, "import Account-Data");
    }
}
