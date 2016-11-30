Tags <a href="#" onclick="toggle('tags');">(+/-)</a>
<form method="post" action="{##form_action##}">
    <fieldset id="tags">
        <legend>Event Tags</legend>
        <input type="checkbox" {##activity_tags_checked##} name="activity[tags]" value="1" /> Allow Tags<br />
        <table>
            <tr>
                <th>Create New</th>
                <th>Available Tags</th>
                <th>Selected Tags</th>
            </tr>
            <tr>
                <td>
                    <input type="text" placeholder="Tag" /><br />
                    <input type="button" value="Create new Tag" />
                </td>
                <td>
                    {##available_tag_names_select##}
                    <select size="7">
                        {##available_tag_names_option##}<option value="{##tag_id##}">{##tag_name##}</option>{##available_tag_names_option##}
                    </select>
                    <input type="button" value="Add Tag &gt;" />
                    {/##available_tag_names_select##}
                </td>
                <td>
                    {##selected_tag_names_select##}
                    <select size="7">
                        {##selected_tag_names_option##}<option value="{##tag_id##}">{##tag_name##}</option>{/##selected_tag_names_option##}
                    </select>
                    <input type="button" value="&lt; Remove Tag" />
                    {/##selected_tag_names_select##}
                </td>
            </tr>
        </table>
        <input type="submit" name="activity[submit][tags]" value="{##submit_text##}" />
    </fieldset>
</form>