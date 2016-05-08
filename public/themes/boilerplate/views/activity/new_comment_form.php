<section class="form">
    <p>You've got something to say?</p>
    <form method="post" action="{##form_action##}">
        <label for="comment_content">{##comment_content_text##}</label>
        <textarea id="comment_content" name="comment[content]" placeholder="{##comment_content_text##}">{##comment_content##}</textarea>
        {##comment_content_validation##}
        <br />
        <input type="submit" name="comment[submit]" value="{##submit_text##}" />
        {##comment_general_validation##}
    </form>
</section>