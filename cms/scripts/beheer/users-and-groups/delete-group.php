<?php

$output->addTitle("Verwijderen");

if (empty($request[2]))
{
    Messager::warning('U heeft geen gebruiker geselecteerd!');
}
else if ($request[2] == 1)
{
    Messager::warning('Sorry, u mag de groep &quot;Iedereen&quot; niet verwijderen.', false);
}
else
{
    $group = $db->query("SELECT `g_id` AS id,
                                `g_name` AS name
                         FROM   `cms_group`
                         WHERE  `g_id` = ".intval($request[2])."
                         LIMIT 1");
	
    if ($db->num_rows($group) == 1)
    {
        $group = $db->fetch_assoc($group);

        if (isset($_POST['confirm']))
        {
            $db->query("DELETE FROM `cms_group`
                        WHERE `g_id` = " . intval($group['id']) . "
                        LIMIT 1");

            $db->query("DELETE FROM `cms_user_group`
                        WHERE `g_id` = " . intval($group['id']));
			
            $db->query("DELETE FROM `cms_item_permission`
                        WHERE `group` = " . ($group['id']));

            Messager::notify('De groep &quot;' . $group['name'] . '&quot; is verwijderd.', false);

            if (isset($_REQUEST['g']))
            {
                $request[2] = $_REQUEST['g'];
                redirect('/beheer/users-and-groups/list-users-in-group/' . $request[2]);
            }
            else
            {
                redirect('/beheer/users-and-groups/list/');
            }
        }
        else
        {
            Messager::notify('Weet je zeker dat je de groep &quot;' . $group['name'] . '&quot; wilt verwijderen?', false);
            ?>
            <form action="./" method="post">
                <?php
                if (isset($_REQUEST['g']))
                {
                    ?>
                    <input type="hidden" name="g" value="<?php echo htmlentities($_REQUEST['g']); ?>" />
                    <?php
                }
                ?>
                <input type="submit" name="confirm" value="Ja, verwijder deze groep." />
            </form>

            <?php
            if (isset($_REQUEST['g']))
            {
                ?>
                <form action="../../list-users-in-group/<?php echo htmlentities($_REQUEST['g']); ?>/" method="post">
                <?php
            }
            else
            {
                ?>
                <form action="../../" method="post">
                <?php
            }
            ?>
                <input type="submit" name="noconfirm" value="Nee, breng me terug naar het groeps overzicht." />
            </form>
            <?php
        }
    }
    else
    {
        Messager::warning('De groep die je wil verwijderen bestaat niet (meer).');
    }
}