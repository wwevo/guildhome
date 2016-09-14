<section class="module">
    <h3>Account characters</h3>
    <table class="gw2api account_characters">
        <tr>
            <th>Lvl</th>
            <th>Name</th>
            <th>Race</th>
            <th>Gender</th>
<!--            <th>Guild</th> //-->
            <th>Age</th>
            <th>Birthday in</th>
            <th>Created</th>
            <th>Deaths</th>
        </tr>
{##characters##}
        <tr>
            <td>{##level##}</td>
            <td>{##name##}</td>
            <td>{##race##}</td>
            <td>{##gender##}</td>
<!--            <td>{##guild##}</td> //-->
            <td>{##age##} days</td>
            <td class="right">{##birthday_in##} days</td>
            <td>{##created##}</td>
            <td class="center">{##deaths##}</td>
        </tr>
{/##characters##}
    </table>
</section>
