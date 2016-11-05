Class registration <a href="#" onclick="toggle('class_registration');">(+/-)</a>
<form method="post" action="{##form_action##}">
    <fieldset id="class_registration">
        <legend>Class registration</legend>
        <input type="checkbox" {##activity_class_registration_checked##}  name="activity[class_registration]" value="1" /> Class registration<br />
        <input type="checkbox" {##activity_selectable_roles_checked##}  name="activity[selectable_roles]" value="1" /> Set selectable roles<br />
        <fieldset>
            <legend>Please set up the required roles</legend>
            <span>...and don't forget to actually select the desired ones :) Choices will be lost on Preview</span><br />
            {##roles_select_option##}
            <input type="checkbox" {##option_selected##} name="activity[roles][]" value="{##option_value##}" /> {##option_text##}<br />
            {/##roles_select_option##}
            <input type="text" name="activity[add_role_title]" placeholder="{##add_role_title_text##}" value="{##add_new_role_title##}" /> {##add_role_title_validation##}<br />
            <input type="submit" name="activity[submit][submit_add_role]" value="{##submit_add_role_text##}" />
            <input type="submit" name="activity[submit][delete_selected_roles]" value="{##delete_selected_roles_text##}" /><br />
            <input type="submit" name="activity[submit][save_roles]" value="{##submit_save_roles_text##}" />
        </fieldset>
    </fieldset>
</form>