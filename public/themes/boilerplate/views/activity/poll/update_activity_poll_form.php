<section class="form">
    <form method="post" action="{##form_action##}">
        <fieldset>
            <legend>General event settings</legend>
            <input type="text" name="activity[title]" placeholder="{##activity_title_text##}" value="{##activity_title##}" /> {##activity_title_validation##}<br />
            <textarea id="activity_content" name="activity[content]" placeholder="{##activity_content_text##}">{##activity_content##}</textarea>
            {##activity_content_validation##}<br />
            Date: <input type="date" name="activity[date]" placeholder="Date" value="{##activity_date##}" />
            Time: <input type="time" name="activity[time]" placeholder="Time" value="{##activity_time##}" /><br />
            <input type="checkbox" {##activity_comments_checked##} name="activity[comments]" value="1" /> Allow comments for this event<br />
        </fieldset>
        <fieldset>
            <legend>Event signups</legend>
            <input {##activity_signups_min_checked##} type="checkbox" name="activity[signups_min]" value="1" /> Minimum signups for event to happen
            <input type="number" name="activity[signups_min_val]" value="{##signups_min_val##}" /><br />
        </fieldset>
        <fieldset>
            <legend>Post itttt!</legend>
            <!-- <input disabled="disabled" type="submit" name="activity[preview]" value="{##preview_text##}" /> //-->
            <input disabled="disabled" type="submit" name="activity[draft]" value="{##draft_text##}" /> //-->
            <!-- <input type="submit" name="activity[submit]" value="{##submit_text##}" />
            <!-- <input disabled="disabled" type="checkbox" name="activity[template_activated]" value="template_activated" /> Save as event template //-->
        </fieldset>
        {##activity_general_validation##}
    </form>
</section>