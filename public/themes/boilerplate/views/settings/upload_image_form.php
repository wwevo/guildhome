<section class="settings form">
    <form method="post" enctype="multipart/form-data" action="{##form_action##}">
        <input type="file" name="setting_avatar[value]" />
        <input type="submit" name="setting_{##setting_key##}[submit]" value="{##setting_submit_text##}" />
        <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
        <input type="hidden" name="target_url" value="{##target_url##}" />
    </form>
</section>