<section class="form">
    <p>Tell your guildmates what you are up to.</p>
    <form method="post" action="{##form_action##}">
        <fieldset>
            <legend>You just HAVE to change something, don't you?</legend>
            <label for="activity_content">{##activity_content_text##}</label>
            <textarea id="activity_content" name="activity[content]" placeholder="{##activity_content_text##}">{##activity_content##}</textarea>
            {##activity_content_validation##}
            <input type="checkbox" {##activity_comments_checked##} name="activity[comments]" value="1" /> Allow comments for this event<br />
            <input disabled="disabled" type="submit" name="activity[preview]" value="{##preview_text##}" />
            <input disabled="disabled" type="submit" name="activity[draft]" value="{##draft_text##}" />
            <input type="submit" name="activity[submit]" value="{##submit_text##}" />
        </fieldset>
        {##activity_general_validation##}
    </form>
</section>