<section class="module">
    <h3>All Users</h3>
    <table>
        <tr>
            <th>username</th>
            <th>rank</th>
        </tr>
{##users##}
        <tr>
            <td><a href="/profile/{##username##}">{##username##}</a></td>
            <td>{##rank_description##}</td>
        </tr>
{/##users##}
    </table>
</section>