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
                    <input type="text" name="activity[tags][create]" placeholder="Tag" /><br />
                    <input type="submit" name="activity[tags][submit][create]" value="Create new Tag" />
                </td>
                <td>
                    {##available_tag_names_select##}
                    <select name="activity[tags][add][]" size="7" multiple="multiple">
                        {##available_tag_names_option##}<option value="{##tag_id##}">{##tag_name##}</option>{/##available_tag_names_option##}
                    </select><br />
                    <input type="submit" name="activity[tags][submit][select]" value="Add Tag &gt;" />
                    {/##available_tag_names_select##}
                </td>
                <td>
                    {##selected_tag_names_select##}
                    <select name="activity[tags][remove][]" size="7" multiple="multiple">
                        {##selected_tag_names_option##}<option value="{##tag_id##}">{##tag_name##}</option>{/##selected_tag_names_option##}
                    </select><br />
                    <input type="submit" name="activity[tags][submit][deselect]" value="&lt; Remove Tag" />
                    {/##selected_tag_names_select##}
                </td>
            </tr>
        </table>
        <input type="submit" name="activity[tags][submit]" value="{##submit_text##}" />
    </fieldset>
</form>