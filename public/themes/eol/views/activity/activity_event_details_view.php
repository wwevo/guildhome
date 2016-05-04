<article class="activity">
    <header>
        <h3>{##activity_title##}</h3>
        <section class="date">
            on <strong>{##activity_date##}</strong> at <strong>{##activity_time##}</strong><br />
        </section>
    </header>
    
    <section class="meta">
        {##activity_type##} event organized by {##activity_owner##}
    </section>
    
    <section class="description">
        {##activity_content##}
    </section>


    {##signups_activated##}
    <section>
        {##signups##}
    </section>
    {/##signups_activated##}

    {##activity_logged_in##}
    <form method="post" action="{##signup##}">
        <input type="submit" name="activity[signup]" value="{##signup_text##}" />
    </form>

    <form method="post" action="{##signout##}">
        <input type="submit" name="activity[signout]" value="{##signout_text##}" />
    </form>
    {/##activity_logged_in##}

</article>
