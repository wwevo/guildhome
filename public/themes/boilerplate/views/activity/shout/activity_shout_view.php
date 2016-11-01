{##activity_message##}
{##activity_loop##}
<article class="activity{##css##}">
    <section class="activity_details">
        <img src="{##avatar##}" width="40" height="40" alt="avatar of user {##activity_identity##}" />
        on {##activity_published##},<br />
        <strong>{##activity_identity##}</strong> posted <strong>{##activity_type##}</strong>.
    </section>
    <section class="content clearfix">
        <section class="details">
            {##activity_content##}
        </section>
    </section>
    {##activity_not_logged_in##}
    <section class="not_logged_in">
        {##comment_link##}<a href="{##comment_link_url##}">{##comment_link_text##}</a>{/##comment_link##}
    </section>
    {/##activity_not_logged_in##}
    {##activity_logged_in##}
    <section class="logged_in">
        {##edit_link##}<a href="{##edit_link_url##}">{##edit_link_text##}</a>{/##edit_link##} |
        {##delete_link##}<a href="{##delete_link_url##}">{##delete_link_text##}</a>{/##delete_link##}
    </section>
    {/##activity_logged_in##}
</article>
{/##activity_loop##}
