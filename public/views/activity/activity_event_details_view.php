<section>
    {##activity_title##}<br />
    {##activity_content##}
    Date: {##activity_date##}<br />
    Time: {##activity_time##}<br />
    {##activity_logged_in##}
    <form method="post" action="{##form_action##}">
        <input type="submit" name="activity[signup]" value="{##signup_text##}" />
        <input type="submit" name="activity[signout]" value="{##signout_text##}" />
    </form>
    {/##activity_logged_in##}
    {##activity_admin##}
        {##admin_content##}
    {/##activity_admin##}
</section>
