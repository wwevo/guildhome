{##activity_message##}
{##activity_loop##}
<article class="activity">
    <img src="{##avatar##}" width="40" height="40" alt="avatar of user {##activity_identity##}" />
    on {##activity_published##},<br />
    <strong>{##activity_identity##}</strong> posted <strong>{##activity_type##}</strong>.
    <section class="content">
        {##activity_content##}
    </section>
    <a href="{##comment_link##}">{##comment_link_text##}</a>
    {##activity_details##}
    <a href="{##details_link##}">{##details_link_text##}</a>
    {/##activity_details##}
    {##activity_logged_in##}
    <section class="logged_in">
        <a href="{##update_link##}">{##update_link_text##}</a>
        <a href="{##delete_link##}">{##delete_link_text##}</a>
    </section>
    {/##activity_logged_in##}
</article>
{/##activity_loop##}
