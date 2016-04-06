<section class="form">
    <p>Tell your guildmates what you are up to.</p>
    <form method="post" action="{##form_action##}">
        <label for="activity_content">{##activity_content_text##}</label>
        <textarea id="activity_content" name="activity[content]" placeholder="{##activity_content_text##}">{##activity_content##}</textarea>
        {##activity_content_validation##}
        <br />
        <input type="submit" name="activity[submit]" value="{##submit_text##}" />
        {##activity_general_validation##}
    </form>
</section>