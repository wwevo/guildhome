<section class="form">
    <p>Tell your guildmates what you are up to.</p>
    <form method="post" action="{##form_action##}">
        <fieldset>
            <legend>Tell us</legend>
            <label for="activity_content">{##activity_content_text##}</label>
            <textarea id="activity_content" name="activity[content]" placeholder="{##activity_content_text##}">{##activity_content##}</textarea>
            {##activity_content_validation##}
            <p>You can use <a href="https://en.wikipedia.org/wiki/Markdown">Markdown</a> to spice up your activities if you like.</p>
            <input type="checkbox" {##activity_comments_checked##} name="activity[comments]" value="1" /> Allow comments for this shout<br />
            <input type="submit" name="activity[submit]" value="{##submit_text##}" />
            <input type="checkbox" {##activity_preview_checked##} name="activity[preview]" value="1" /> {##preview_text##}
        </fieldset>
        {##activity_general_validation##}
    </form>
</section>