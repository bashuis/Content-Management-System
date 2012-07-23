<?php

$output->addTitle("Verwijderen");

if (empty($request[2]))
{
    Messager::warning('U heeft geen gebruiker geselecteerd!');
}
else if(User::id() == $request[2])
{
    Messager::warning('Sorry, u mag uzelf niet verwijderen.');
}
else
{
    $user = $db->query("SELECT `u_id` AS id,
                               `u_name` AS name
                        FROM   `cms_user`
                        WHERE  `u_id` = " . intval($request[2]) . "
                        LIMIT 1");
	
    if ($db->num_rows($user) == 1)
    {
        $user = $db->fetch_assoc($user);

        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if (isset($_POST['confirm']))
            {
                deleteUser($user['id']);

                Messager::ok('De gebruiker &quot;' . $user['name'] . '&quot; is succesvol verwijderd.', false, true);
            }

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
            Messager::notify('Weet u zeker dat u de gebruiker &quot;' . $user['name'] . '&quot; wilt verwijderen?', false);
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
                <input type="submit" name="confirm" value="Ja, verwijder deze gebruiker." />
                <input type="submit" name="deny" value="Nee, verwijder deze gebruiker niet." />
            </form>
        <?php
        }
    }
    else
    {
        Messager::warning('De gebruiker die je wil verwijderen bestaat niet (meer).', false, true);
        redirect('/beheer/users-and-groups/list/');
    }
}