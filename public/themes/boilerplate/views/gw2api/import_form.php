<h3>Import data</h3>
<p>Please only use the import function as sparsely as possible. It is still in early development and causes heavy use of the gw2 api.</p>
<p><b>Excessive use may result in a temporary block for our website.</b></p>
<p>This function may not work with very large rosters. I have successfully tested it with 15 character Slots and the 'account', 'characters' and 'inventory' scopes, which downloaded about 60kb of data. Without the 'inventory' scope it's just 4kb.</p>
<p>I recommend to create an gw2 api key just containing 'account' and 'characters' for now, specially if you have a lot of Characters :)</p>
<section class="settings form">
    <form method="post" action="{##form_action##}">
        <input type="submit" name="gw2api_import[submit]" value="{##gw2api_import_submit_text##}" />
    </form>
</section>