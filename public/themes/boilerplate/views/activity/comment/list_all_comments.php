{##comment_message##}
{##comment_loop##}
<article class="comment">
    <section class="comment_details">
        <img src="{##avatar##}" width="40" height="40" alt="avatar of user {##comment_identity##}" />
        on {##comment_published##},<br />
        <strong>{##comment_identity##}</strong> posted <strong>{##activity_type##}</strong>
    </section>
    <section class="content">
        {##comment_content##}
    </section>
    {##comment_logged_in##}
    <section class="logged_in">
        {##edit_link##}<a href="{##edit_link_url##}">{##edit_link_text##}</a>{/##edit_link##} |
        {##delete_link##}<a href="{##delete_link_url##}">{##delete_link_text##}</a>{/##delete_link##}
    </section>
    {/##comment_logged_in##}
</article>
{/##comment_loop##}
