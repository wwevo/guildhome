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
            {##details_link_area##}
            <a href="{##details_link##}">{##details_link_text##}</a>
            {/##details_link_area##}
            {##activity_content##}{##link_more##}<a href="{##link_more_link##}" class="more">{##link_more_link_text##}</a>{/##link_more##}
        </section>
        <section class="signups clearfix">
            {##activity_event_date##}<br />
            {##activity_signups##}
        </section>
    </section>
    {##activity_not_logged_in##}
    <section class="not_logged_in">
        <a href="{##comment_link##}">{##comment_link_text##}</a>
    </section>
    {/##activity_not_logged_in##}
    {##activity_logged_in##}
    <section class="logged_in">
        <a href="{##update_link##}">{##update_link_text##}</a> | <a href="{##delete_link##}">{##delete_link_text##}</a>
    </section>
    {/##activity_logged_in##}
</article>
{/##activity_loop##}
