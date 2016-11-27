<section class="form">
    <form method="post" action="{##form_action##}">
        <label for="login_username">
            {##login_username_text##}
            <input id="login_username" type="text" size="24" name="login[username]" value="{##login_username##}" placeholder="{##login_username_text##}" />
            {##login_username_validation##}
        </label>
        <label for="login_password">
            {##login_password_text##}
            <input id="login_password" type="password" size="24" name="login[password]" autocomplete="off" placeholder="{##login_password_text##}" />
            {##login_password_validation##}
        </label>
        <input type="submit" id="login_submit" name="login[submit]" value="{##login_submit_text##}" />
    </form>
</section>
