<section class="form">
    <p>{##change_password_general_information##}</p>
    <form method="post" action="{##form_action##}">
        <label for="old_password">
            {##current_password_text##}
            <input id="current_password" type="password" size="32" name="change_password[password_current]" placeholder="{##current_password_text##}" value="{##current_password##}" autocomplete="off" />
            {##current_password_validation##}
        </label>
        <label for="new_password">
            {##new_password_text##}
            <input id="new_password" type="password" size="32" name="change_password[password_new]" placeholder="{##new_password_text##}" autocomplete="off" />
            {##new_password_validation##}
        </label>
        <label for="new_password_repeat">
            {##new_password_repeat_text##}
            <input id="new_password_repeat" type="password" size="32" name="change_password[password_repeat]" placeholder="{##new_password_repeat_text##}" autocomplete="off" />
            {##new_password_repeat_validation##}
        </label>
        <input type="submit" name="change_password[submit]" value="{##change_password_submit_text##}" />
        <!-- <input type="reset"  name="change_password[cancel]" value="{##register_cancel_text##}" /> //-->
        {##change_password_general_validation##}
    </form>
</section>