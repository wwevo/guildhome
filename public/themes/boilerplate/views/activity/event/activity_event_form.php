<script type="text/javascript">
function toggle(div){
    if (document.getElementById(div).className == 'hidden') {
        document.getElementById(div).className = '';
    } else {
        document.getElementById(div).className = 'hidden';
    }
}
</script>
<section class="form">
    <p>Together we are strong!</p>
    <form method="post" action="{##form_action##}">
        <fieldset>
            <legend>General event settings</legend>
            <input type="text" name="activity[title]" placeholder="{##activity_title_text##}" value="{##activity_title##}" /> {##activity_title_validation##}<br />
            <select name="activity[event_type]">
                <option value="1">Missions</option>
                <option value="2">Raid</option>
                <option value="3">Fractal</option>
                <option value="4">PvP</option>
                <option value="5">Fun</option>
            </select>
            <textarea id="activity_content" name="activity[content]" placeholder="{##activity_content_text##}">{##activity_content##}</textarea>
            {##activity_content_validation##}<br />
            Date: <input type="date" name="activity[date]" placeholder="Date" value="{##activity_date##}" />
            Time: <input type="time" name="activity[time]" placeholder="Time" value="{##activity_time##}" /> {##activity_date_validation##}<br />
            <input type="checkbox" {##activity_comments_checked##} name="activity[comments]" value="1" /> Allow comments for this event<br />
        </fieldset>
        Signups <a href="#" onclick="toggle('signups');">(+/-)</a>
        <fieldset id="signups" class="hidden">
            <legend>Event signups</legend>
            <input type="checkbox" {##activity_signups_checked##} name="activity[signups]" value="1" /> Allow signups<br />
            <input {##activity_signups_min_checked##} type="checkbox" name="activity[signups_min]" value="1" /> Minimum signups for event to happen
            <input type="number" name="activity[signups_min_val]" value="{##signups_min_val##}" /><br />
            <input {##activity_signups_max_checked##} type="checkbox" name="activity[signups_max]" value="1" /> Maximum signups for this event
            <input type="number" name="activity[signups_max_val]" value="{##signups_max_val##}" /><br />
            <input {##activity_keep_signups_open_checked##} type="checkbox" name="activity[keep_signups_open]" value="1" /> Keep signups open beyond set limit<br />
        </fieldset>
        Class registration <a href="#" onclick="toggle('class_registration');">(+/-)</a>
        <fieldset id="class_registration" class="hidden">
            <legend>Class registration</legend>
            <input type="checkbox" {##activity_class_registration_checked##}  name="activity[class_registration]" value="1" /> Class registration<br />
            <input type="checkbox" {##activity_selectable_roles_checked##}  name="activity[selectable_roles]" value="1" /> Set selectable roles<br />
            <fieldset>
                <legend>Please set up the required roles</legend>
                <span>...and don't forget to actually select the desired ones :)</span><br />
                {##roles_select_option##}
                <input type="checkbox" {##option_selected##} name="activity[roles][]" value="{##option_value##}" /> {##option_text##}<br />
                {/##roles_select_option##}
                <input type="text" name="activity[add_role_title]" placeholder="{##add_role_title_text##}" value="{##add_new_role_title##}" /> {##add_role_title_validation##}<br />
                <input type="submit" name="activity[submit][submit_add_role]" value="{##submit_add_role_text##}" />
                <input type="submit" name="activity[submit][delete_selected_roles]" value="{##delete_selected_roles_text##}" /><br />
<!--                <input type="checkbox" name="activity[multiple_roles]" value="multiple_roles" /> Allow participants to register for multiple roles //-->
            </fieldset>
        </fieldset>
        <fieldset>
            <legend>Post itttt!</legend>
<!--            <input disabled="disabled" type="submit" name="activity[preview]" value="{##preview_text##}" />
            <input disabled="disabled" type="submit" name="activity[draft]" value="{##draft_text##}" /> //-->
            <input type="submit" name="activity[submit]" value="{##submit_text##}" />
            <input type="submit" name="activity[preview]" value="{##preview_text##}" />
<!--            <input disabled="disabled" type="checkbox" name="activity[template_activated]" value="template_activated" /> Save as event template //-->
        </fieldset>
        {##activity_general_validation##}
    </form>
</section>