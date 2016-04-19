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
            Date: <input type="date" name="activity[date]" placeholder="Date" value="" />
            Time: <input type="time" name="activity[time]" placeholder="Time" value="" /><br />
            <input disabled="disabled" type="checkbox" name="activity[calendar]" value="calendar" /> Display event on calendar<br />
            <input disabled="disabled" type="checkbox" name="activity[repeat]" value="repeat" /> Repeat Events 
            <select disabled="disabled" name="activity[schedule]">
                <option value="1">Weekly</option>
                <option value="2">Biweekly</option>
                <option value="3">Monthly</option>
            </select><br />
            <input type="checkbox" {##activity_comments_checked##} name="activity[comments]" value="1" /> Allow comments for this event<br />
        </fieldset>
        <fieldset>
            <legend>Event signups</legend>
            <input type="checkbox" {##activity_signups_checked##} name="activity[signups]" value="1" /> Allow signups<br />
            <input disabled="disabled" type="checkbox" name="activity[signups_min]" value="signups_min" /> Minimum signups for event to happen
            <input type="number" name="activity[signups_min_val]" value="{##signups_min_val##}" /><br />
            <input disabled="disabled" type="checkbox" name="activity[signups_max]" value="signups_max" /> Maximum signups for this event
            <input type="number" name="activity[signups_max_val]" value="{##signups_max_val##}" /><br />
            <input disabled="disabled" type="checkbox" name="activity[signups]" value="signups" /> Keep signups open beyond set limit<br />
        </fieldset>
        <fieldset disabled="disabled">
            <legend>Class registration</legend>
            <input disabled="disabled" type="checkbox" name="activity[signups]" value="signups" /> Class registration<br />
            <input disabled="disabled" type="checkbox" name="activity[signups_min]" value="signups_min" /> Set selectable roles<br />
            <select disabled="disabled" name="activity[roles]" multiple="multiple">
                <option value="1">Healer</option>
                <option value="2">Tank</option>
                <option value="3">DPS</option>
                <option value="4">Leecher</option>
            </select><br />
            <input disabled="disabled" type="checkbox" name="activity[multiple_roles]" value="multiple_roles" /> Allow participants to register for multiple choices<br />
        </fieldset>
        <fieldset>
            <legend>Post itttt!</legend>
            <input disabled="disabled" type="submit" name="activity[preview]" value="{##preview_text##}" />
            <input disabled="disabled" type="submit" name="activity[draft]" value="{##draft_text##}" />
            <input type="submit" name="activity[submit]" value="{##submit_text##}" />
            <input disabled="disabled" type="checkbox" name="activity[template_activated]" value="template_activated" /> Save as event template
        </fieldset>
        {##activity_general_validation##}
    </form>
</section>