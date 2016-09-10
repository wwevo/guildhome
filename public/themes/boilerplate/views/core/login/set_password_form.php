<section class="form">
    <p>{##set_password_general_information##}</p>
    <form method="post" action="{##form_action##}">
        <label for="old_password">
            {##current_password_text##}
        </label>
        <label for="new_password">
            {##new_password_text##}
            <input id="new_password" type="password" size="32" name="set_password[password_new]" placeholder="{##new_password_text##}" autocomplete="off" />
            {##new_password_validation##}
        </label>
        <label for="new_password_repeat">
            {##new_password_repeat_text##}
            <input id="new_password_repeat" type="password" size="32" name="set_password[password_repeat]" placeholder="{##new_password_repeat_text##}" autocomplete="off" />
            {##new_password_repeat_validation##}
        </label>
        <input type="submit" name="set_password[submit]" value="{##set_password_submit_text##}" />
        {##set_password_general_validation##}
    </form>
</section>