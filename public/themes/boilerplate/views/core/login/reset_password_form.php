
<section class="form">
    <form method="post" action="{##form_action##}">
        <label for="registered_email">
            <p>{##registered_email_description##}</p>
            {##registered_email_text##}
            <input id="registered_email" type="text" size="32" name="reset_password[registered_email]" placeholder="{##registered_email_text##}" value="{##registered_email##}" autocomplete="off" />
            {##registered_email_validation##}
        </label>
        <input type="submit" name="reset_password[submit]" value="{##reset_password_submit_text##}" />
    </form>
</section>