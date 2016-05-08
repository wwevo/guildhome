<section class="form">
    <p>Delete activity</p>
    <form method="post" action="{##form_action##}">
        <label for="activity_content">{##activity_content_text##}</label>
        <p>{##activity_content##}</p>
        <br />
        <input type="submit" name="activity[submit]" value="{##submit_text##}" />
        <input type="submit" name="activity[submit]" value="{##cancel_text##}" />
        {##activity_general_validation##}
    </form>
</section>