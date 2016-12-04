Tags <a href="#" onclick="toggle('tags');">(+/-)</a>
<form method="post" action="{##form_action##}">
    <fieldset id="tags">
        <legend>Event Tags</legend>
        <blockquote>
            In development, please don't use it yet ^^<br />
            Just have it here to 'show' it so you can comment on it.
        </blockquote>
        <input type="checkbox" {##activity_tags_checked##} name="activity[tags][enabled]" value="1" /> Allow Tags<br />
        <table>
            <tr>
                <th>Create New</th>
                <th>Available Tags</th>
                <th>Selected Tags</th>
            </tr>
            <tr>
                <td>
                    <input type="text" name="activity[tags][name]" placeholder="Tag" /><br />
                </td>
                <td>
                    {##available_tag_names_select##}
                    <select name="activity[tags][select][]" size="7" multiple="multiple">
                        {##available_tag_names_option##}<option value="{##tag_id##}">{##tag_name##}</option>{/##available_tag_names_option##}
                    </select><br />
                    {/##available_tag_names_select##}
                </td>
                <td>
                    {##selected_tag_names_select##}
                    <select name="activity[tags][remove][]" size="7" multiple="multiple">
                        {##selected_tag_names_option##}<option value="{##tag_id##}">{##tag_name##}</option>{/##selected_tag_names_option##}
                    </select><br />
                    {/##selected_tag_names_select##}
                </td>
            </tr>
            <tr>
                <td>
                    <input type="submit" name="activity[tags][submit][create]" value="+ Create new Tag" />
                </td>
                <td class="right">
                    <input type="submit" name="activity[tags][submit][select]" value="Add Tag &rarr;" />
                </td>
                <td>
                    <input type="submit" name="activity[tags][submit][remove]" value="&larr; Remove Tag" />
                </td>
            </tr>
        </table>
        <input type="submit" name="activity[tags][submit][save]" value="{##submit_text##}" />
    </fieldset>
</form>