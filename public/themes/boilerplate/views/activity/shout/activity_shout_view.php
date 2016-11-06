{##activity_message##}
{##activity_loop##}
<article class="activity shout">
    <h2>Activity-Shout</h2>
    <header class="activity_details">
        <img src="{##avatar##}" width="40" height="40" alt="avatar of user {##activity_identity##}" />
        on {##activity_published##},<br />
        <strong>{##activity_identity##}</strong> posted <strong>{##activity_type##}</strong>.
    </header>
    <section class="content clearfix">
        <h2>Shout</h2>
        {##activity_content##}
    </section>
    {##activity_not_logged_in##}
    <nav class="not_logged_in">
        {##comment_link##}<a href="{##comment_link_url##}">{##comment_link_text##}</a>{/##comment_link##}
    </nav>
    {/##activity_not_logged_in##}
    {##activity_logged_in##}
    <nav class="logged_in">
        {##edit_link##}<a href="{##edit_link_url##}">{##edit_link_text##}</a>{/##edit_link##} |
        {##delete_link##}<a href="{##delete_link_url##}">{##delete_link_text##}</a>{/##delete_link##}
    </nav>
    {/##activity_logged_in##}
</article>
{/##activity_loop##}
