<section class="settings form">
    <p>Only valid keys with allowed scopes will be accepted for now: (guild, account, characters)</p>
    <form method="post" action="{##form_action##}">
        <label for="update_setting">
            <input id="update_setting" type="text" size="64" name="gw2_api_account_add_key[api_key]" value="{##setting_value##}" />
            {##update_setting_validation##}
        </label>
        <input type="submit" name="gw2_api_account_add_key[submit]" value="{##setting_submit_text##}" />
        <input type="hidden" name="target_url" value="{##target_url##}" />
    </form>
</section>