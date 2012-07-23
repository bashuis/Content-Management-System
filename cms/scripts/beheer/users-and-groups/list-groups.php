
<h2>Groepen</h2>
<?php

$groups = $db->query("SELECT `g_id` AS id,
                             `g_name` AS name
                      FROM   `cms_group`
					  WHERE  `g_id` <> 1
					  AND    `g_id` <> 200
                      ORDER BY `g_name`");

if ($db->num_rows($groups) == 0)
{
    Messager::warning('Er zijn momenteel (nog) geen groepen.');
}
else
{
    ?>
    <table>
        <tr>
            <th>#</th>
            <th>Naam:</th>
            <th>Opties:</th>
        </tr>
        <?php
        $classTicker = true;
        while ($group = $db->fetch_assoc($groups))
        {
            ?>
            <tr class="<?php echo ($classTicker ? 'odd' : 'even'); $classTicker = !$classTicker; ?>">
                <td><?php echo $group['id']; ?></td>
                <td><?php echo stripslashes( $group['name'] ); ?></td>
                <td>
                    <a href="/beheer/users-and-groups/edit-group/<?php echo $group['id']; ?>/">
                        <img src="/icons/fugues/icons/pencil.png" style="vertical-align: bottom;" alt="Bewerk groep" />
                    </a>

                    <a href="/beheer/users-and-groups/delete-group/<?php echo $group['id']; ?>/">
                        <img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijder groep" />
                    </a>

                    <a href="/beheer/users-and-groups/list-users-in-group/<?php echo $group['id']; ?>/">
                        <img src="/icons/fugues/icons/eye.png" style="vertical-align: bottom;" alt="Bekijk groep" />
                    </a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}