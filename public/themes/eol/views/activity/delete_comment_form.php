<section class="form">
    <p>Delete comment</p>
    <form method="post" action="{##form_action##}">
        <label for="activity_content">{##comment_content_text##}</label>
        <p>{##comment_content##}</p>
        <br />
        <input type="submit" name="comment[submit]" value="{##submit_text##}" />
        <input type="submit" name="comment[submit]" value="{##cancel_text##}" />
        {##comment_general_validation##}
    </form>
</section>