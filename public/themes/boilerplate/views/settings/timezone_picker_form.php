<section class="settings form">
    <form method="post" action="{##form_action##}">
        <select name="setting_timezone[value]">
        {##timezone_select_option##}
            <option value="{##option_value##}"{##option_selected##}>{##option_text##}</option>
        {/##timezone_select_option##}
        </select>
        <input type="submit" name="setting_timezone[submit]" value="{##timezone_submit_text##}" />
    </form>
</section>