<section class="settings form">
    <form method="post" action="{##form_action##}">
        <label for="update_setting">
            {##setting_key##}
            <input type="date" name="setting_{##setting_key##}[value]" placeholder="Date" value="{##setting_value##}" />
            {##update_setting_validation##}
        </label>
        <input type="submit" name="setting_{##setting_key##}[submit]" value="{##setting_submit_text##}" />
        <input type="hidden" name="target_url" value="{##target_url##}" />
    </form>
</section>