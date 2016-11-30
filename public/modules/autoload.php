<?php
require_once "login/Login.php";
    require_once "login/Login_Password.php";
require_once "login/Register.php";

require_once "profile/Profile.php";
    require_once "profile/Profile_Settings.php";
    require_once "profile/Profile_Characters.php";

require_once "settings/Settings.php";
require_once "identity/Identity.php";

require_once "activity/Activity.php";
    require_once "activity/shout/Activity_Shout.php";
    require_once "activity/event/Activity_Event.php";
        require_once "activity/event/widgets/Activity_Event_Widgets.php";
        require_once "activity/event/signups/Activity_Event_Signups.php";
            require_once "activity/event/signups/widgets/Activity_Event_Signups_Widgets.php";
        require_once "activity/event/tags/Activity_Event_Tags_Model.php";
        require_once "activity/event/tags/Activity_Event_Tags_View.php";
        require_once "activity/event/tags/Activity_Event_Tags.php";
    require_once "activity/example/Activity_Example.php";
    require_once "activity/actionmsg/Activity_ActionMsg.php";
    require_once "activity/Activity_Comment.php";
    require_once "activity/poll/PollModel.php";
    require_once "activity/Activity_Stream.php";

require_once "pages/Pages.php";

require_once "gw2api/Gw2Api_Interface.php";
require_once "gw2api/Gw2Api_Abstract.php";
require_once "gw2api/Gw2Api.php";
    require_once "gw2api/accounts/Gw2Api_Accounts_Interface.php";
    require_once "gw2api/accounts/Gw2Api_Accounts_Model.php";
    require_once "gw2api/accounts/Gw2Api_Accounts_View.php";
    require_once "gw2api/accounts/Gw2Api_Accounts.php";
        require_once "gw2api/accounts/characters/Gw2Api_Characters_Model.php";
        require_once "gw2api/accounts/characters/Gw2Api_Characters_Widgets.php";
        require_once "gw2api/accounts/characters/Gw2Api_Characters_View.php";
        require_once "gw2api/accounts/characters/Gw2Api_Characters.php";
        require_once "gw2api/accounts/guilds/Gw2Api_Guilds_Interface.php";
        require_once "gw2api/accounts/guilds/Gw2Api_Guilds_Model.php";
        require_once "gw2api/accounts/guilds/Gw2Api_Guilds_View.php";
        require_once "gw2api/accounts/guilds/Gw2Api_Guilds.php";
            require_once "gw2api/accounts/guilds/members/Gw2Api_Members_Model.php";
            require_once "gw2api/accounts/guilds/members/Gw2Api_Members_Widgets.php";
            require_once "gw2api/accounts/guilds/members/Gw2Api_Members_View.php";
            require_once "gw2api/accounts/guilds/members/Gw2Api_Members.php";
        require_once "gw2api/accounts/keys/Gw2Api_Keys_Interface.php";
        require_once "gw2api/accounts/keys/Gw2Api_Keys_Model.php";
        require_once "gw2api/accounts/keys/Gw2Api_Keys_View.php";
        require_once "gw2api/accounts/keys/Gw2Api_Keys.php";

require_once "dev/Dev.php";
require_once "config/Database_view.php";
require_once "config/Database.php";
