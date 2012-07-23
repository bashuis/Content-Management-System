<h2>Gebruikers</h2>
<?php

$users = $db->query("SELECT `u_id` AS id,
                            `u_fullname` AS fullname,
                            `u_name` AS name,
                            `u_active` AS active,
                            `u_admin` AS admin
                     FROM   `cms_user`
                     WHERE  `u_verified` = 1
                     AND    `u_id` <> -1
                     AND    `u_id` <> 0
                     ORDER BY `u_fullname`");

if ($db->num_rows($users) == 0)
{
    Messager::warning('Er zijn momenteel (nog) geen gebruikers.');
}
else
{
    ?>
    <table>
        <tr>
            <th>#</th>
            <th>Naam:</th>
            <th>Inlognaam:</th>
            <th>Eigenschappen:</th>
            <th>Opties:</th>
        </tr>
        <?php
        $classTicker = true;
        while ($user = $db->fetch_assoc($users))
        {
            ?>
            <tr class="<?php echo ($classTicker ? 'odd' : 'even'); $classTicker = !$classTicker; ?>">
                <td><?php echo $user['id']; ?></td>
                <td><?php echo stripslashes( $user['fullname'] ); ?></td>
                <td><?php echo stripslashes( $user['name'] ); ?></td>
                <td>
                    <img src="/icons/fugues/icons/<?php echo ($user['active'] == 1 ? 'status' : 'status-busy' ); ?>.png" alt="<?php echo ($user['active'] == 1 ? 'Actief' : 'Inactief' ); ?>" />
                    <?php
                    if (User::isAdmin($user['id']))
                    {
                        ?><img src="/icons/fugues/icons/star.png" alt="Beheerder" /><?php
                    }
                    else if (User::isInAdminGroup($user['id']))
                    {
                        ?><img src="/icons/fugues/icons/star-half.png" alt="Semi Beheerder" /><?php
                    }
                    ?>
                </td>
                <td>
                    <a href="/beheer/users-and-groups/edit-user/<?php echo $user['id']; ?>/"><img src="/icons/fugues/icons/pencil.png" style="vertical-align: bottom;" alt="Bewerk gebruiker" /></a>
                    
                    <?php if ($user['id'] != user::id())
                    {
                        ?>
                        <a href="/beheer/users-and-groups/delete-user/<?php echo $user['id']; ?>/"><img src="/icons/fugues/icons/cross.png" style="vertical-align: bottom;" alt="Verwijder gebruiker" /></a>
                        <?php
                    }
                    ?>

                    <a href="/beheer/users-and-groups/reset-password/<?php echo $user['id']; ?>/"><img src="/icons/fugues/icons/key.png" style="vertical-align: bottom;" alt="Wachtwoord herstellen" /></a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}